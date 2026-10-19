<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use App\Models\VendorProfile;
use App\Services\GeocodingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventLocationGoogleMapsTest extends TestCase
{
    use RefreshDatabase;

    private User $vendorUser;
    private VendorProfile $vendorProfile;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Concerts & Festivals',
            'slug' => 'concerts',
        ]);

        $this->vendorUser = User::factory()->create([
            'role' => 'vendor',
        ]);

        $this->vendorProfile = VendorProfile::create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'Apex Event Studio',
            'slug' => 'apex-event-studio',
            'city' => 'New York',
            'country' => 'USA',
        ]);
    }

    public function test_google_maps_api_key_configured_in_services()
    {
        $key = config('services.google.maps_api_key');
        $this->assertNotEmpty($key);
        $this->assertEquals('AIzaSyD4kvUarFgOzuY1iOcS51rJVHsXOdFLlZg', $key);
    }

    public function test_vendor_event_create_page_renders_map_picker_and_no_manual_coordinates_inputs()
    {
        $response = $this->actingAs($this->vendorUser)->get(route('vendor.events.create'));

        $response->assertStatus(200);

        // Verify Google Map picker is present
        $response->assertSee('Pin Event Location on Map');
        $response->assertSee('Search venue, landmark, or street address on Google Maps...');
        $response->assertSee('googleMapCanvas');

        // Verify hidden internal coordinate fields exist
        $response->assertSee('type="hidden" name="latitude"', false);
        $response->assertSee('type="hidden" name="longitude"', false);

        // Verify manual text entry fields for latitude and longitude have been removed
        $response->assertDontSee('Latitude (Optional for Map)');
        $response->assertDontSee('Longitude (Optional for Map)');
    }

    public function test_vendor_event_edit_page_renders_map_picker_with_existing_coordinates()
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Neon Nights Live',
            'slug' => 'neon-nights-live',
            'venue_name' => 'Brooklyn Steel',
            'address' => '319 Frost Street',
            'city' => 'Brooklyn',
            'country' => 'USA',
            'latitude' => 40.7195,
            'longitude' => -73.9388,
            'event_date' => Carbon::now()->addDays(10)->toDateString(),
            'start_time' => '20:00',
            'category_id' => $this->category->id,
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_ADULTS],
        ]);

        $response = $this->actingAs($this->vendorUser)->get(route('vendor.events.edit', $event));

        $response->assertStatus(200);
        $response->assertSee('Pin Event Location on Map');
        $response->assertSee('40.7195');
        $response->assertSee('-73.9388');

        // Hidden fields present, manual numeric fields removed
        $response->assertSee('type="hidden" name="latitude"', false);
        $response->assertSee('type="hidden" name="longitude"', false);
    }

    public function test_vendor_can_create_event_with_coordinates_from_map_picker()
    {
        $payload = [
            'title' => 'Times Square Indie Fest',
            'venue_name' => 'Times Square Plaza',
            'address' => 'Broadway & 7th Ave',
            'city' => 'New York',
            'country' => 'USA',
            'latitude' => 40.7580,
            'longitude' => -73.9855,
            'event_date' => Carbon::now()->addDays(14)->toDateString(),
            'start_time' => '17:00',
            'category_id' => $this->category->id,
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_ALL_AGES],
        ];

        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), $payload);

        $response->assertRedirect();

        $event = Event::where('title', 'Times Square Indie Fest')->first();
        $this->assertNotNull($event);
        $this->assertEquals(40.7580, (float) $event->latitude);
        $this->assertEquals(-73.9855, (float) $event->longitude);
    }

    public function test_coordinates_are_automatically_stored_internally_when_omitted_by_vendor()
    {
        // Mock GeocodingService to return coordinates for address
        $mockGeocoding = $this->mock(GeocodingService::class);
        $mockGeocoding->shouldReceive('geocode')
            ->once()
            ->andReturn([
                'lat' => 52.5200,
                'lng' => 13.4050,
                'display_name' => 'Alexanderplatz, Berlin, Germany',
            ]);

        $payload = [
            'title' => 'Berlin Community Meetup',
            'venue_name' => 'Alexanderplatz Center',
            'address' => 'Alexanderplatz 1',
            'city' => 'Berlin',
            'country' => 'Germany',
            'latitude' => null,
            'longitude' => null,
            'event_date' => Carbon::now()->addDays(20)->toDateString(),
            'start_time' => '14:00',
            'category_id' => $this->category->id,
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_ALL_AGES],
        ];

        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), $payload);

        $response->assertRedirect();

        $event = Event::where('title', 'Berlin Community Meetup')->first();
        $this->assertNotNull($event);
        // Assert coordinates were resolved and stored internally
        $this->assertEquals(52.5200, (float) $event->latitude);
        $this->assertEquals(13.4050, (float) $event->longitude);
    }

    public function test_public_event_details_displays_google_maps_location_and_navigation_actions()
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Empire State Rooftop Jazz',
            'slug' => 'empire-state-rooftop-jazz',
            'venue_name' => 'Sky Lounge',
            'address' => '350 5th Ave',
            'city' => 'New York',
            'country' => 'USA',
            'latitude' => 40.7484,
            'longitude' => -73.9857,
            'event_date' => Carbon::now()->addDays(12)->toDateString(),
            'start_time' => '19:00',
            'category_id' => $this->category->id,
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_ALL_AGES],
        ]);

        $response = $this->get(route('events.show', $event->slug));

        $response->assertStatus(200);

        // Google Maps Location Section
        $response->assertSee('event-location-card');
        $response->assertSee('Venue & Location');
        $response->assertSee('Sky Lounge');
        $response->assertSee('350 5th Ave');
        $response->assertSee('New York');

        // Google Maps embed iframe
        $response->assertSee('https://maps.google.com/maps?q=40.7484,-73.9857&z=15&output=embed', false);

        // Action links: Open in Google Maps & Get Directions
        $response->assertSee('Open in Google Maps');
        $response->assertSee('Get Directions');
        $response->assertSee('destination=40.7484,-73.9857', false);
    }
}
