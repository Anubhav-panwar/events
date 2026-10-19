<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EventRecommendationService
{
    /**
     * Default weights for recommendation signals (summing to 100).
     */
    protected float $categoryWeight = 30.0;
    protected float $typeTagsWeight = 25.0;
    protected float $locationWeight = 20.0;
    protected float $audienceWeight = 10.0;
    protected float $dateWeight = 10.0;
    protected float $priceWeight = 5.0;

    /**
     * Get ranked event recommendations for a specific event.
     *
     * @param Event $event The currently viewed event
     * @param User|null $user The currently authenticated user (if any)
     * @param int $limit Maximum number of recommendations to return
     * @return Collection
     */
    public function getRecommendations(Event $event, ?User $user = null, int $limit = 4): Collection
    {
        $today = Carbon::today()->toDateString();

        // 1. Efficient candidate query at the database level
        $query = Event::query()
            ->select('events.*')
            ->where('events.id', '!=', $event->id)
            ->where('events.status', 'published')
            ->whereDate('events.event_date', '>=', $today)
            ->with(['vendorProfile', 'ticketTypes', 'media', 'category']);

        if ($user) {
            $query->withExists([
                'saves as is_saved' => fn ($q) => $q->where('users.id', $user->id),
            ]);
        }

        // Prioritize promising candidates in SQL before applying the detailed scoring engine
        $categoryId = $event->category_id ?? 0;
        $city = $event->city ?? '';
        $query->orderByRaw('(CASE WHEN events.category_id = ? THEN 0 WHEN events.city = ? THEN 1 ELSE 2 END)', [$categoryId, $city])
            ->orderBy('events.event_date', 'asc')
            ->limit(60);

        $candidates = $query->get();

        if ($candidates->isEmpty()) {
            return collect();
        }

        // 2. Build user personalization context if logged in
        $userContext = $this->buildUserContext($user);

        // 3. Score each candidate
        foreach ($candidates as $candidate) {
            $scoring = $this->calculateRelevance($candidate, $event, $userContext);
            $candidate->recommendation_score = $scoring['score'];
            $candidate->recommendation_breakdown = $scoring['breakdown'];
            $candidate->recommendation_reasons = $scoring['reasons'];
        }

        // 4. Filter only positive relevance scores & sort deterministically
        $ranked = $candidates
            ->filter(fn ($c) => ($c->recommendation_score ?? 0) > 0)
            ->sort(function ($a, $b) {
                // 1. Score descending
                if ($b->recommendation_score <=> $a->recommendation_score) {
                    return $b->recommendation_score <=> $a->recommendation_score;
                }
                // 2. Event date ascending (closer date first)
                $dateA = $a->event_date?->timestamp ?? 0;
                $dateB = $b->event_date?->timestamp ?? 0;
                if ($dateA <=> $dateB) {
                    return $dateA <=> $dateB;
                }
                // 3. Deterministic ID ascending
                return $a->id <=> $b->id;
            });

        return $ranked->values()->take($limit);
    }

    /**
     * Calculate relevance score between a candidate event and the current event.
     *
     * @param Event $candidate
     * @param Event $current
     * @param array|null $userContext
     * @return array
     */
    public function calculateRelevance(Event $candidate, Event $current, ?array $userContext = null): array
    {
        $rawScore = 0.0;
        $availableMax = 0.0;
        $breakdown = [];
        $reasons = [];

        // --- SIGNAL A: Category Matching (Weight: 30%) ---
        $hasCategory = !empty($current->category_id);
        if ($hasCategory) {
            $availableMax += $this->categoryWeight;
            if ($candidate->category_id && (int) $candidate->category_id === (int) $current->category_id) {
                $rawScore += $this->categoryWeight;
                $breakdown['category'] = $this->categoryWeight;
                $reasons[] = 'Same event category';
            } else {
                $breakdown['category'] = 0.0;
            }
        } else {
            $breakdown['category'] = null; // Missing on current event; will be normalized
        }

        // --- SIGNAL B: Event Type and Tags / Interests (Weight: 25%) ---
        $currentTags = $this->normalizeTags($current->tags);
        $candidateTags = $this->normalizeTags($candidate->tags);
        $hasTags = count($currentTags) > 0;
        $hasType = !empty($current->event_type);

        if ($hasTags || $hasType) {
            $availableMax += $this->typeTagsWeight;
            $typeTagScore = 0.0;

            if ($hasTags) {
                $intersection = array_intersect($currentTags, $candidateTags);
                $tagRatio = count($intersection) / max(count($currentTags), 1);
                $tagScore = round($tagRatio * 18.0, 2);
                $typeTagScore += $tagScore;

                if (count($intersection) > 0) {
                    $reasons[] = 'Shared interests: ' . implode(', ', array_slice($intersection, 0, 3));
                }

                if ($hasType && !empty($candidate->event_type) && strcasecmp((string)$current->event_type, (string)$candidate->event_type) === 0) {
                    $typeTagScore += 7.0;
                    $reasons[] = 'Same format (' . $candidate->event_type . ')';
                }
            } else {
                // If current event has no tags, use event_type matching for full typeTags weight
                if ($hasType && !empty($candidate->event_type) && strcasecmp((string)$current->event_type, (string)$candidate->event_type) === 0) {
                    $typeTagScore += $this->typeTagsWeight;
                    $reasons[] = 'Same format (' . $candidate->event_type . ')';
                }
            }

            $typeTagScore = min($this->typeTagsWeight, $typeTagScore);
            $rawScore += $typeTagScore;
            $breakdown['type_tags'] = $typeTagScore;
        } else {
            $breakdown['type_tags'] = null;
        }

        // --- SIGNAL C: Location-Based Proximity (Weight: 20%) ---
        $hasCoordinates = $current->latitude !== null && $current->longitude !== null;
        $hasCity = !empty($current->city);
        $hasCountry = !empty($current->country);
        $hasAddress = !empty($current->address);

        if ($hasCoordinates || $hasCity || $hasCountry || $hasAddress) {
            $availableMax += $this->locationWeight;
            $locationScore = 0.0;

            if ($hasCoordinates && $candidate->latitude !== null && $candidate->longitude !== null) {
                $distanceKm = $this->calculateDistanceKm(
                    (float) $current->latitude,
                    (float) $current->longitude,
                    (float) $candidate->latitude,
                    (float) $candidate->longitude
                );

                if ($distanceKm <= 15.0) {
                    $locationScore = 20.0;
                    $reasons[] = 'Nearby location (< 15 km)';
                } elseif ($distanceKm <= 50.0) {
                    $locationScore = 16.0;
                    $reasons[] = 'Nearby location (< 50 km)';
                } elseif ($distanceKm <= 100.0) {
                    $locationScore = 12.0;
                    $reasons[] = 'In the same region (< 100 km)';
                } elseif ($distanceKm <= 250.0) {
                    $locationScore = 6.0;
                } elseif ($distanceKm <= 500.0) {
                    $locationScore = 2.0;
                }
            }

            // Fallback or text-based location comparison if coordinates not close or missing
            if ($locationScore < 12.0) {
                if ($hasCity && !empty($candidate->city)) {
                    $currCity = strtolower(trim($current->city));
                    $candCity = strtolower(trim($candidate->city));
                    if ($currCity === $candCity || str_contains($candCity, $currCity) || str_contains($currCity, $candCity)) {
                        $locationScore = max($locationScore, 18.0);
                        $reasons[] = 'Same city (' . $candidate->city . ')';
                    }
                } elseif ($hasCountry && !empty($candidate->country)) {
                    if (strcasecmp(trim($current->country), trim($candidate->country)) === 0) {
                        $locationScore = max($locationScore, 8.0);
                        $reasons[] = 'Same country';
                    }
                }
            }

            $rawScore += $locationScore;
            $breakdown['location'] = $locationScore;
        } else {
            $breakdown['location'] = null;
        }

        // --- SIGNAL D: Target Audience Matching (Weight: 10%) ---
        $currentAudience = $this->normalizeAudience($current->audience);
        $candidateAudience = $this->normalizeAudience($candidate->audience);
        $hasAudience = count($currentAudience) > 0;

        if ($hasAudience) {
            $availableMax += $this->audienceWeight;
            $audScore = 0.0;

            $hasAllAges = in_array(Event::AUDIENCE_ALL_AGES, $currentAudience, true) ||
                          in_array(Event::AUDIENCE_ALL_AGES, $candidateAudience, true);

            if ($hasAllAges) {
                $audScore = 10.0;
                $reasons[] = 'Compatible target audience (All Ages)';
            } elseif (count(array_intersect($currentAudience, $candidateAudience)) > 0) {
                $audScore = 10.0;
                $reasons[] = 'Matching target audience';
            } elseif (empty($candidateAudience)) {
                $audScore = 4.0; // Candidate has no restrictions; partially compatible
            } else {
                $audScore = 0.0; // Incompatible audience restrictions (e.g. Kids vs Adults)
            }

            $rawScore += $audScore;
            $breakdown['audience'] = $audScore;
        } else {
            $breakdown['audience'] = null;
        }

        // --- SIGNAL E: Date Freshness and Availability (Weight: 10%) ---
        $availableMax += $this->dateWeight;
        $dateScore = 0.0;
        if ($candidate->event_date) {
            $daysDiff = Carbon::today()->diffInDays(Carbon::parse($candidate->event_date), false);
            if ($daysDiff < 0) {
                $dateScore = 0.0;
            } elseif ($daysDiff <= 7) {
                $dateScore = 10.0;
                $reasons[] = 'Happening this week';
            } elseif ($daysDiff <= 30) {
                $dateScore = 8.0;
                $reasons[] = 'Happening this month';
            } elseif ($daysDiff <= 60) {
                $dateScore = 6.0;
            } elseif ($daysDiff <= 120) {
                $dateScore = 4.0;
            } else {
                $dateScore = 2.0;
            }
        }
        $rawScore += $dateScore;
        $breakdown['date'] = $dateScore;

        // --- SIGNAL F: Price Range Matching (Weight: 5%) ---
        $currPrice = $this->resolvePrice($current);
        $candPrice = $this->resolvePrice($candidate);

        if ($currPrice !== null) {
            $availableMax += $this->priceWeight;
            $priceScore = 0.0;

            if ($candPrice !== null) {
                if ($currPrice === 0.0 && $candPrice === 0.0) {
                    $priceScore = 5.0;
                    $reasons[] = 'Free admission';
                } elseif ($currPrice > 0.0 && $candPrice > 0.0) {
                    $diff = abs($candPrice - $currPrice);
                    if ($diff <= 10.0) {
                        $priceScore = 5.0;
                        $reasons[] = 'Similar price range';
                    } elseif ($diff <= 35.0) {
                        $priceScore = 3.5;
                    } elseif ($diff <= 75.0) {
                        $priceScore = 2.0;
                    } else {
                        $priceScore = 1.0;
                    }
                } else {
                    $priceScore = 0.0; // One is free and one is paid
                }
            } else {
                $priceScore = 2.5; // Unknown candidate price, neutral
            }

            $rawScore += $priceScore;
            $breakdown['price'] = $priceScore;
        } else {
            $breakdown['price'] = null;
        }

        // --- WEIGHT NORMALIZATION ---
        // Normalizes available signals to a 100-point scale so missing optional data does not penalize candidates
        $normalizedScore = $availableMax > 0
            ? ($rawScore / $availableMax) * 100.0
            : $rawScore;

        // --- USER PERSONALIZATION BONUS (up to 15 bonus points) ---
        $personalizationBonus = 0.0;
        if (!empty($userContext)) {
            if ($candidate->category_id && in_array((int)$candidate->category_id, $userContext['preferred_categories'], true)) {
                $personalizationBonus += 6.0;
                $reasons[] = 'Matches your preferred category';
            }

            if ($candidate->vendor_profile_id && in_array((int)$candidate->vendor_profile_id, $userContext['preferred_vendors'], true)) {
                $personalizationBonus += 6.0;
                $reasons[] = 'From an organizer you follow';
            }

            if (!empty($userContext['preferred_audiences']) && count(array_intersect($candidateAudience, $userContext['preferred_audiences'])) > 0) {
                $personalizationBonus += 3.0;
            }
        }

        $finalScore = round(min(115.0, $normalizedScore + $personalizationBonus), 2);

        $breakdown['personalization'] = $personalizationBonus;
        $breakdown['raw_score'] = round($rawScore, 2);
        $breakdown['available_max'] = $availableMax;
        $breakdown['normalized_score'] = round($normalizedScore, 2);
        $breakdown['total'] = $finalScore;

        return [
            'score' => $finalScore,
            'breakdown' => $breakdown,
            'reasons' => array_unique($reasons),
        ];
    }

    /**
     * Compute great-circle distance between two GPS coordinates using the Haversine formula.
     *
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float Distance in kilometers
     */
    public function calculateDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    /**
     * Build user interest context from saved events, past bookings, and followed vendors.
     */
    protected function buildUserContext(?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        try {
            // Categories & vendors from saved events
            $savedEvents = $user->savedEvents()
                ->select('events.id', 'events.category_id', 'events.vendor_profile_id', 'events.audience')
                ->get();

            $savedCategoryIds = $savedEvents->pluck('category_id')->filter()->map(fn($v) => (int)$v)->all();
            $savedVendorIds = $savedEvents->pluck('vendor_profile_id')->filter()->map(fn($v) => (int)$v)->all();
            $savedAudiences = $savedEvents->pluck('audience')
                ->filter()
                ->flatMap(fn($a) => $this->normalizeAudience($a))
                ->unique()
                ->all();

            // Categories & vendors from bookings/orders
            $orders = $user->orders()
                ->whereIn('status', ['paid', 'completed', 'active', 'confirmed'])
                ->orWhereNull('status')
                ->select('event_id', 'vendor_profile_id')
                ->get();

            $orderedEventIds = $orders->pluck('event_id')->filter()->unique()->all();
            $orderedVendorIds = $orders->pluck('vendor_profile_id')->filter()->map(fn($v) => (int)$v)->all();

            $orderedCategoryIds = [];
            if (!empty($orderedEventIds)) {
                $orderedCategoryIds = Event::whereIn('id', $orderedEventIds)
                    ->pluck('category_id')
                    ->filter()
                    ->map(fn($v) => (int)$v)
                    ->all();
            }

            // Followed vendors
            $followedVendorIds = $user->followedVendors()
                ->pluck('vendor_profiles.id')
                ->map(fn($v) => (int)$v)
                ->all();

            return [
                'preferred_categories' => array_values(array_unique(array_merge($savedCategoryIds, $orderedCategoryIds))),
                'preferred_vendors' => array_values(array_unique(array_merge($savedVendorIds, $orderedVendorIds, $followedVendorIds))),
                'preferred_audiences' => array_values(array_unique($savedAudiences)),
            ];
        } catch (\Throwable $e) {
            // Non-critical fallback if personalization fails
            return null;
        }
    }

    /**
     * Normalize tags into an array of lowercase trimmed strings.
     */
    protected function normalizeTags(mixed $tags): array
    {
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }

        if (!is_array($tags)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            return is_string($item) ? strtolower(trim($item)) : null;
        }, $tags)));
    }

    /**
     * Normalize audience into recognized canonical category names.
     */
    protected function normalizeAudience(mixed $audience): array
    {
        if (is_string($audience)) {
            $audience = explode(',', $audience);
        }

        if (!is_array($audience)) {
            return [];
        }

        $normalized = [];
        foreach ($audience as $item) {
            if (is_string($item)) {
                $clean = Event::normalizeAudienceCategory($item);
                if ($clean) {
                    $normalized[] = $clean;
                }
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Resolve effective event price (free = 0.0, paid = numeric price, null if undetermined).
     */
    protected function resolvePrice(Event $event): ?float
    {
        if ($event->event_type === 'free') {
            return 0.0;
        }

        if ($event->base_price !== null) {
            return (float) $event->base_price;
        }

        if ($event->relationLoaded('ticketTypes') && $event->ticketTypes->isNotEmpty()) {
            $minPrice = $event->ticketTypes->min('price');
            if ($minPrice !== null) {
                return (float) $minPrice;
            }
        }

        return null;
    }
}
