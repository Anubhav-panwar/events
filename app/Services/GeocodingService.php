<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Geocode an address/place name into latitude and longitude coordinates.
     * Uses Google Geocoding API if key configured, falling back to Nominatim/OSM.
     */
    public function geocode(string $place): ?array
    {
        $place = trim($place);
        if ($place === '') {
            return null;
        }

        $cacheKey = 'geo:' . md5(strtolower($place));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($place) {
            $googleKey = config('services.google.maps_api_key');
            if (!empty($googleKey)) {
                try {
                    $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                        'address' => $place,
                        'key' => $googleKey,
                    ]);

                    if ($response->ok() && $response->json('status') === 'OK') {
                        $first = $response->json('results.0');
                        if ($first && isset($first['geometry']['location']['lat'], $first['geometry']['location']['lng'])) {
                            return [
                                'lat' => (float) $first['geometry']['location']['lat'],
                                'lng' => (float) $first['geometry']['location']['lng'],
                                'display_name' => (string) ($first['formatted_address'] ?? $place),
                                'source' => 'google',
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    Log::debug('Google Geocoding failed, falling back to Nominatim: ' . $e->getMessage());
                }
            }

            // Fallback to OpenStreetMap Nominatim
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'EventMarketplace/1.0 (contact@example.com)',
                ])->timeout(6)->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $place,
                    'format' => 'jsonv2',
                    'limit' => 1,
                ]);

                if ($response->ok()) {
                    $first = $response->json('0');
                    if ($first && isset($first['lat'], $first['lon'])) {
                        return [
                            'lat' => (float) $first['lat'],
                            'lng' => (float) $first['lon'],
                            'display_name' => (string) ($first['display_name'] ?? $place),
                            'source' => 'nominatim',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('Nominatim geocoding failed: ' . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Reverse geocode coordinates into a structured address description.
     */
    public function reverseGeocode(float $lat, float $lng): ?array
    {
        $cacheKey = 'reverse_geo:' . round($lat, 4) . ':' . round($lng, 4);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($lat, $lng) {
            $googleKey = config('services.google.maps_api_key');
            if (!empty($googleKey)) {
                try {
                    $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                        'latlng' => "{$lat},{$lng}",
                        'key' => $googleKey,
                    ]);

                    if ($response->ok() && $response->json('status') === 'OK') {
                        $first = $response->json('results.0');
                        if ($first) {
                            $components = $first['address_components'] ?? [];
                            $city = null;
                            $country = null;
                            foreach ($components as $comp) {
                                $types = $comp['types'] ?? [];
                                if (in_array('locality', $types) || in_array('postal_town', $types)) {
                                    $city = $comp['long_name'];
                                }
                                if (in_array('country', $types)) {
                                    $country = $comp['long_name'];
                                }
                            }
                            return [
                                'formatted_address' => $first['formatted_address'] ?? '',
                                'city' => $city,
                                'country' => $country,
                                'source' => 'google',
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    Log::debug('Google Reverse Geocoding failed: ' . $e->getMessage());
                }
            }

            // Fallback to Nominatim Reverse
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'EventMarketplace/1.0 (contact@example.com)',
                ])->timeout(6)->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'jsonv2',
                ]);

                if ($response->ok()) {
                    $json = $response->json();
                    $address = $json['address'] ?? [];
                    return [
                        'formatted_address' => $json['display_name'] ?? '',
                        'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['county'] ?? null,
                        'country' => $address['country'] ?? null,
                        'source' => 'nominatim',
                    ];
                }
            } catch (\Throwable $e) {
                Log::debug('Nominatim reverse geocoding failed: ' . $e->getMessage());
            }

            return null;
        });
    }
}
