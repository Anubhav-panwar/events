<?php

use App\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Ensure column exists
        if (!Schema::hasColumn('events', 'audience')) {
            Schema::table('events', function (Blueprint $table) {
                $table->json('audience')->nullable()->after('tags');
            });
        }

        // 2. Safe backward-compatible data migration for existing events
        $events = DB::table('events')->get();
        foreach ($events as $event) {
            $rawAudience = $event->audience;
            $currentList = [];

            if (!empty($rawAudience)) {
                $decoded = json_decode($rawAudience, true);
                if (is_array($decoded)) {
                    $currentList = $decoded;
                } elseif (is_string($rawAudience)) {
                    $currentList = array_map('trim', explode(',', $rawAudience));
                }
            }

            $mapped = [];
            foreach ($currentList as $item) {
                $canonical = Event::normalizeAudienceCategory((string) $item);
                if ($canonical) {
                    $mapped[] = $canonical;
                }
            }

            // If no canonical category matched, use sensible defaults based on title/tags/description or default to All Ages
            if (empty($mapped)) {
                $combinedText = strtolower(($event->title ?? '') . ' ' . ($event->description ?? '') . ' ' . ($event->tags ?? ''));
                if (str_contains($combinedText, 'cocktail') || str_contains($combinedText, 'wine') || str_contains($combinedText, 'beer') || str_contains($combinedText, 'night') || str_contains($combinedText, 'rooftop')) {
                    $mapped = [Event::AUDIENCE_ADULTS];
                } elseif (str_contains($combinedText, 'kid') || str_contains($combinedText, 'children') || str_contains($combinedText, 'family') || str_contains($combinedText, 'carnival') || str_contains($combinedText, 'street food')) {
                    $mapped = [Event::AUDIENCE_ALL_AGES, Event::AUDIENCE_KIDS, Event::AUDIENCE_TEENS, Event::AUDIENCE_ADULTS];
                } elseif (str_contains($combinedText, 'esport') || str_contains($combinedText, 'gaming') || str_contains($combinedText, 'robot') || str_contains($combinedText, 'drone')) {
                    $mapped = [Event::AUDIENCE_TEENS, Event::AUDIENCE_ADULTS];
                } else {
                    $mapped = [Event::AUDIENCE_ALL_AGES];
                }
            }

            $mapped = array_values(array_unique($mapped));

            DB::table('events')
                ->where('id', $event->id)
                ->update(['audience' => json_encode($mapped)]);
        }
    }

    public function down(): void
    {
        // Safe no-op to prevent data loss
    }
};
