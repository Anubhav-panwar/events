<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use App\Models\VendorProfile;
use App\Services\EventRecommendationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRecommendationTest extends TestCase
{
    use RefreshDatabase;

    private User $vendorUser;
    private VendorProfile $vendorProfile;
    private Category $musicCategory;
    private Category $sportsCategory;
    private EventRecommendationService $recommendationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recommendationService = app(EventRecommendationService::class);

        $this->vendorUser = User::factory()->create([
            'role' => 'vendor',
        ]);

        $this->vendorProfile = VendorProfile::create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'Starlight Productions',
            'slug' => 'starlight-productions',
            'city' => 'Berlin',
            'country' => 'Germany',
        ]);

        $this->musicCategory = Category::create([
            'name' => 'Concerts & Live Music',
            'slug' => 'music',
        ]);

        $this->sportsCategory = Category::create([
            'name' => 'Sports & Fitness',
            'slug' => 'sports',
        ]);
    }

    private function createEvent(array $overrides = []): Event
    {
        static $counter = 1;
        $idx = $counter++;

        return Event::create(array_merge([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => "Event {$idx}",
            'slug' => "event-{$idx}-" . uniqid(),
            'description' => "Description for event {$idx}",
            'venue_name' => 'Main Arena',
            'event_date' => Carbon::now()->addDays(10)->toDateString(),
            'start_time' => '18:00',
            'end_time' => '22:00',
            'address' => 'Alexanderplatz 1',
            'city' => 'Berlin',
            'country' => 'Germany',
            'latitude' => 52.5200,
            'longitude' => 13.4050,
            'category_id' => $this->musicCategory->id,
            'capacity' => 500,
            'status' => 'published',
            'event_type' => 'paid',
            'base_price' => 45.00,
            'tags' => ['music', 'concert', 'live'],
            'audience' => [Event::AUDIENCE_ALL_AGES],
        ], $overrides));
    }

    public function test_event_details_page_renders_recommended_events_section()
    {
        $current = $this->createEvent([
            'title' => 'Rock Festival 2026',
            'slug' => 'rock-festival-2026',
            'category_id' => $this->musicCategory->id,
        ]);

        $candidate1 = $this->createEvent([
            'title' => 'Indie Band Night',
            'category_id' => $this->musicCategory->id,
        ]);

        $candidate2 = $this->createEvent([
            'title' => 'Jazz Evening Live',
            'category_id' => $this->musicCategory->id,
        ]);

        $response = $this->get(route('events.show', $current->slug));

        $response->assertStatus(200);
        $response->assertSee('You May Also Like');
        $response->assertSee('Indie Band Night');
        $response->assertSee('Jazz Evening Live');
    }

    public function test_current_event_is_never_recommended()
    {
        $current = $this->createEvent([
            'title' => 'Solitary Event',
            'slug' => 'solitary-event',
        ]);

        $recommendations = $this->recommendationService->getRecommendations($current, null, 10);

        $this->assertFalse($recommendations->contains('id', $current->id));
    }

    public function test_cancelled_draft_and_past_events_are_excluded()
    {
        $current = $this->createEvent(['title' => 'Main Active Event']);

        $cancelled = $this->createEvent([
            'title' => 'Cancelled Showcase',
            'status' => 'cancelled',
        ]);

        $draft = $this->createEvent([
            'title' => 'Draft Unpublished',
            'status' => 'draft',
        ]);

        $past = $this->createEvent([
            'title' => 'Past Finished Event',
            'status' => 'published',
            'event_date' => Carbon::now()->subDays(5)->toDateString(),
        ]);

        $recommendations = $this->recommendationService->getRecommendations($current, null, 10);

        $this->assertFalse($recommendations->contains('id', $cancelled->id));
        $this->assertFalse($recommendations->contains('id', $draft->id));
        $this->assertFalse($recommendations->contains('id', $past->id));
    }

    public function test_category_matching_prioritizes_same_category_events()
    {
        $current = $this->createEvent([
            'title' => 'Symphony Orchestra Gala',
            'category_id' => $this->musicCategory->id,
        ]);

        $sameCategory = $this->createEvent([
            'title' => 'Classical Piano Recital',
            'category_id' => $this->musicCategory->id,
            'event_date' => Carbon::now()->addDays(20)->toDateString(),
        ]);

        $differentCategory = $this->createEvent([
            'title' => 'Football Championship Match',
            'category_id' => $this->sportsCategory->id,
            'event_date' => Carbon::now()->addDays(20)->toDateString(),
        ]);

        $recommendations = $this->recommendationService->getRecommendations($current, null, 4);

        $this->assertEquals($sameCategory->id, $recommendations->first()->id);
        $sameRec = $recommendations->firstWhere('id', $sameCategory->id);
        $diffRec = $recommendations->firstWhere('id', $differentCategory->id);
        $this->assertNotNull($sameRec);
        $this->assertNotNull($diffRec);
        $this->assertGreaterThan($diffRec->recommendation_score, $sameRec->recommendation_score);
    }

    public function test_location_proximity_prioritizes_nearby_events()
    {
        $current = $this->createEvent([
            'title' => 'Berlin Tech Summit',
            'city' => 'Berlin',
            'latitude' => 52.5200,
            'longitude' => 13.4050,
            'category_id' => $this->musicCategory->id,
        ]);

        // Nearby event in Berlin (~3 km away)
        $nearbyEvent = $this->createEvent([
            'title' => 'Berlin Indie Session',
            'city' => 'Berlin',
            'latitude' => 52.5000,
            'longitude' => 13.3900,
            'category_id' => $this->musicCategory->id,
            'event_date' => Carbon::now()->addDays(15)->toDateString(),
        ]);

        // Far away event in Munich (~500 km away)
        $farEvent = $this->createEvent([
            'title' => 'Munich Outdoor Jam',
            'city' => 'Munich',
            'latitude' => 48.1351,
            'longitude' => 11.5820,
            'category_id' => $this->musicCategory->id,
            'event_date' => Carbon::now()->addDays(15)->toDateString(),
        ]);

        $recommendations = $this->recommendationService->getRecommendations($current, null, 4);

        $this->assertEquals($nearbyEvent->id, $recommendations->first()->id);
        $nearRec = $recommendations->firstWhere('id', $nearbyEvent->id);
        $farRec = $recommendations->firstWhere('id', $farEvent->id);
        $this->assertNotNull($nearRec);
        $this->assertNotNull($farRec);
        $this->assertGreaterThan($farRec->recommendation_score, $nearRec->recommendation_score);
    }

    public function test_audience_compatibility_is_evaluated()
    {
        $current = $this->createEvent([
            'title' => 'Children Cartoon Magic Show',
            'audience' => [Event::AUDIENCE_KIDS],
            'category_id' => $this->musicCategory->id,
        ]);

        $compatibleKids = $this->createEvent([
            'title' => 'Kids Puppet Theatre',
            'audience' => [Event::AUDIENCE_KIDS],
            'category_id' => $this->musicCategory->id,
            'event_date' => Carbon::now()->addDays(12)->toDateString(),
        ]);

        $incompatibleAdults = $this->createEvent([
            'title' => 'Late Night Standup 21+',
            'audience' => [Event::AUDIENCE_ADULTS],
            'category_id' => $this->musicCategory->id,
            'event_date' => Carbon::now()->addDays(12)->toDateString(),
        ]);

        $recommendations = $this->recommendationService->getRecommendations($current, null, 4);

        $this->assertEquals($compatibleKids->id, $recommendations->first()->id);
        $compatRec = $recommendations->firstWhere('id', $compatibleKids->id);
        $incompatRec = $recommendations->firstWhere('id', $incompatibleAdults->id);
        $this->assertNotNull($compatRec);
        $this->assertNotNull($incompatRec);
        $this->assertGreaterThan($incompatRec->recommendation_score, $compatRec->recommendation_score);
    }

    public function test_tag_similarity_increases_relevance_score()
    {
        $current = $this->createEvent([
            'title' => 'Yoga and Mindfulness Retreat',
            'tags' => ['yoga', 'wellness', 'meditation'],
            'category_id' => $this->sportsCategory->id,
        ]);

        $similarTags = $this->createEvent([
            'title' => 'Sound Bath & Meditation',
            'tags' => ['meditation', 'wellness'],
            'category_id' => $this->sportsCategory->id,
            'event_date' => Carbon::now()->addDays(14)->toDateString(),
        ]);

        $unrelatedTags = $this->createEvent([
            'title' => 'Powerlifting Bootcamp',
            'tags' => ['gym', 'crossfit'],
            'category_id' => $this->sportsCategory->id,
            'event_date' => Carbon::now()->addDays(14)->toDateString(),
        ]);

        $recommendations = $this->recommendationService->getRecommendations($current, null, 4);

        $this->assertEquals($similarTags->id, $recommendations->first()->id);
        $similarRec = $recommendations->firstWhere('id', $similarTags->id);
        $unrelatedRec = $recommendations->firstWhere('id', $unrelatedTags->id);
        $this->assertNotNull($similarRec);
        $this->assertNotNull($unrelatedRec);
        $this->assertGreaterThan($unrelatedRec->recommendation_score, $similarRec->recommendation_score);
    }

    public function test_logged_out_guests_can_view_recommendations()
    {
        $current = $this->createEvent(['title' => 'Public Community Picnic']);
        $related = $this->createEvent(['title' => 'Public Parks Meetup']);

        $this->assertGuest();
        $response = $this->get(route('events.show', $current->slug));

        $response->assertStatus(200);
        $response->assertSee('Public Parks Meetup');
    }

    public function test_logged_in_user_receives_personalization_bonus()
    {
        $user = User::factory()->create();

        // User saved an event in sports category
        $savedEvent = $this->createEvent([
            'title' => 'Prior Saved Marathon',
            'category_id' => $this->sportsCategory->id,
        ]);
        $user->savedEvents()->attach($savedEvent->id);

        $current = $this->createEvent([
            'title' => 'Neutral Showcase Event',
            'category_id' => null, // neutral category
        ]);

        $sportsCandidate = $this->createEvent([
            'title' => 'Running Club 10K',
            'category_id' => $this->sportsCategory->id,
            'event_date' => Carbon::now()->addDays(20)->toDateString(),
        ]);

        $musicCandidate = $this->createEvent([
            'title' => 'Random Music Session',
            'category_id' => $this->musicCategory->id,
            'event_date' => Carbon::now()->addDays(20)->toDateString(),
        ]);

        // As guest
        $guestRecs = $this->recommendationService->getRecommendations($current, null, 4);
        $guestSports = $guestRecs->firstWhere('id', $sportsCandidate->id);

        // As authenticated user
        $userRecs = $this->recommendationService->getRecommendations($current, $user, 4);
        $userSports = $userRecs->firstWhere('id', $sportsCandidate->id);

        $this->assertNotNull($guestSports);
        $this->assertNotNull($userSports);
        $this->assertGreaterThan($guestSports->recommendation_score, $userSports->recommendation_score);
    }

    public function test_missing_optional_fields_do_not_cause_errors()
    {
        $sparseCurrent = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Sparse Current Event',
            'slug' => 'sparse-current-event',
            'description' => 'Sparse description',
            'venue_name' => 'Sparse Venue',
            'event_date' => Carbon::now()->addDays(5)->toDateString(),
            'start_time' => '12:00',
            'end_time' => '14:00',
            'status' => 'published',
            // Missing: category_id, city, country, latitude, longitude, tags, audience, base_price
        ]);

        $sparseCandidate = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Sparse Candidate Event',
            'slug' => 'sparse-candidate-event',
            'description' => 'Candidate description',
            'venue_name' => 'Candidate Venue',
            'event_date' => Carbon::now()->addDays(8)->toDateString(),
            'start_time' => '15:00',
            'end_time' => '17:00',
            'status' => 'published',
        ]);

        $recommendations = $this->recommendationService->getRecommendations($sparseCurrent, null, 4);

        $this->assertNotEmpty($recommendations);
        $this->assertEquals($sparseCandidate->id, $recommendations->first()->id);
        $this->assertGreaterThan(0, $recommendations->first()->recommendation_score);
    }

    public function test_json_recommendations_endpoint_returns_ranked_results()
    {
        $current = $this->createEvent(['title' => 'API Primary Event']);
        $rec1 = $this->createEvent(['title' => 'API Related Event One']);
        $rec2 = $this->createEvent(['title' => 'API Related Event Two']);

        $response = $this->getJson(route('events.recommendations', $current->slug));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'event' => ['id', 'slug', 'title'],
            'count',
            'recommendations' => [
                '*' => [
                    'id',
                    'slug',
                    'title',
                    'score',
                    'reasons',
                    'url',
                ],
            ],
        ]);

        $this->assertEquals(2, $response->json('count'));
    }

    public function test_empty_state_when_no_other_events_exist()
    {
        $current = $this->createEvent(['title' => 'Sole Event In System']);

        $response = $this->get(route('events.show', $current->slug));

        $response->assertStatus(200);
        // The section is cleanly hidden when no other events exist
        $response->assertDontSee('recommended-events-section');
    }

    public function test_recommendations_section_renders_in_all_languages()
    {
        $current = $this->createEvent([
            'title' => 'Multilingual Hero Concert',
            'category_id' => $this->musicCategory->id,
        ]);
        $related = $this->createEvent([
            'title' => 'Multilingual Sister Show',
            'category_id' => $this->musicCategory->id,
        ]);

        // English
        $responseEn = $this->get(route('events.show', ['slug' => $current->slug, 'lang' => 'en']));
        $responseEn->assertStatus(200);
        $responseEn->assertSee('You May Also Like');
        $responseEn->assertSee('Recommended For You');

        // German
        $responseDe = $this->get(route('events.show', ['slug' => $current->slug, 'lang' => 'de']));
        $responseDe->assertStatus(200);
        $responseDe->assertSee('Das könnte Ihnen auch gefallen');
        $responseDe->assertSee('Für Sie empfohlen');

        // Italian
        $responseIt = $this->get(route('events.show', ['slug' => $current->slug, 'lang' => 'it']));
        $responseIt->assertStatus(200);
        $responseIt->assertSee('Potrebbero interessarti anche');
        $responseIt->assertSee('Consigliati per te');
    }
}
