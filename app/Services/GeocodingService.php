<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeocodingService
{
    public function geocode(string $place): ?array
    {
        $place = trim($place);
        if ($place === '') {
            return null;
        }

        $cacheKey = 'geo:' . md5(strtolower($place));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($place) {
            $response = Http::withHeaders([
                'User-Agent' => 'EventMarketplace/1.0 (contact@example.com)',
            ])->timeout(8)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $place,
                'format' => 'jsonv2',
                'limit' => 1,
            ]);

            if (!$response->ok()) {
                return null;
            }

            $first = $response->json('0');
            if (!$first || !isset($first['lat'], $first['lon'])) {
                return null;
            }

            return [
                'lat' => (float) $first['lat'],
                'lng' => (float) $first['lon'],
                'display_name' => (string) ($first['display_name'] ?? $place),
            ];
        });
    }
}
