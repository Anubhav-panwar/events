<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventAudienceCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $vendorUser;
    private VendorProfile $vendorProfile;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Music & Concerts',
            'slug' => 'music',
        ]);

        $this->vendorUser = User::factory()->create([
            'role' => 'vendor',
        ]);

        $this->vendorProfile = VendorProfile::create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'Elite Events Co',
            'slug' => 'elite-events-co',
            'city' => 'San Francisco',
            'country' => 'USA',
        ]);
    }

    public function test_vendor_can_create_event_with_single_and_multiple_audience_categories()
    {
        $payload = [
            'title' => 'Family Fun Weekend',
            'description' => 'A wonderful weekend event for children and families.',
            'venue_name' => 'City Park',
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '16:00',
            'address' => '123 Park Lane',
            'city' => 'San Francisco',
            'country' => 'USA',
            'category_id' => $this->category->id,
            'capacity' => 200,
            'status' => 'published',
            'event_type' => 'free',
            'tags' => 'family, fun',
            'audience' => [Event::AUDIENCE_KIDS, Event::AUDIENCE_ALL_AGES],
        ];

        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), $payload);

        $event = Event::where('title', 'Family Fun Weekend')->first();
        $this->assertNotNull($event);
        $this->assertEquals([Event::AUDIENCE_KIDS, Event::AUDIENCE_ALL_AGES], $event->audience);
        $response->assertRedirect(route('events.show', $event->slug));
    }

    public function test_audience_field_is_mandatory_when_creating_event()
    {
        $payload = [
            'title' => 'Missing Audience Event',
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '10:00',
            'status' => 'draft',
            'event_type' => 'free',
            // Missing 'audience'
        ];

        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), $payload);
        $response->assertSessionHasErrors('audience');
    }

    public function test_invalid_audience_categories_are_rejected()
    {
        $payload = [
            'title' => 'Invalid Audience Event',
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '10:00',
            'status' => 'draft',
            'event_type' => 'free',
            'audience' => ['InvalidCategory', 'Aliens'],
        ];

        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), $payload);
        $response->assertSessionHasErrors(['audience.0', 'audience.1']);
    }

    public function test_categories_appear_correctly_on_edit_page_and_can_be_updated()
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Tech Talks For Teens',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'start_time' => '14:00',
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_TEENS],
        ]);

        $editResponse = $this->actingAs($this->vendorUser)->get(route('vendor.events.edit', $event));
        $editResponse->assertOk();
        $editResponse->assertSee('value="Teens"', false);

        // Update to Teens and Adults
        $updatePayload = [
            'title' => 'Tech Talks For Teens and Adults',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'start_time' => '14:00',
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_TEENS, Event::AUDIENCE_ADULTS],
        ];

        $updateResponse = $this->actingAs($this->vendorUser)->put(route('vendor.events.update', $event), $updatePayload);
        $updateResponse->assertRedirect(route('vendor.events.edit', $event));

        $event->refresh();
        $this->assertEquals([Event::AUDIENCE_TEENS, Event::AUDIENCE_ADULTS], $event->audience);
    }

    public function test_event_search_filters_correctly_by_single_audience()
    {
        $kidsEvent = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Kids Magic Show',
            'event_date' => now()->addDays(2),
            'start_time' => '11:00',
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_KIDS],
        ]);

        $adultsEvent = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Wine & Jazz Night',
            'event_date' => now()->addDays(3),
            'start_time' => '20:00',
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_ADULTS],
        ]);

        // Filter by Kids
        $responseKids = $this->get(route('search', ['audience' => [Event::AUDIENCE_KIDS]]));
        $responseKids->assertOk();
        $responseKids->assertSee($kidsEvent->title);
        $responseKids->assertDontSee($adultsEvent->title);

        // Filter by Adults
        $responseAdults = $this->get(route('search', ['audience' => [Event::AUDIENCE_ADULTS]]));
        $responseAdults->assertOk();
        $responseAdults->assertSee($adultsEvent->title);
        $responseAdults->assertDontSee($kidsEvent->title);
    }

    public function test_event_search_uses_or_logic_for_multiple_selected_audiences()
    {
        $kidsEvent = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Puppet Theater',
            'event_date' => now()->addDays(2),
            'start_time' => '11:00',
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_KIDS],
        ]);

        $teensEvent = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Robotics Hackathon',
            'event_date' => now()->addDays(3),
            'start_time' => '13:00',
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_TEENS],
        ]);

        $adultsEvent = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Cocktail Masterclass',
            'event_date' => now()->addDays(4),
            'start_time' => '19:00',
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_ADULTS],
        ]);

        // Filter by Kids OR Teens
        $response = $this->get(route('search', [
            'audience' => [Event::AUDIENCE_KIDS, Event::AUDIENCE_TEENS]
        ]));

        $response->assertOk();
        $response->assertSee($kidsEvent->title);
        $response->assertSee($teensEvent->title);
        $response->assertDontSee($adultsEvent->title);
    }

    public function test_audience_filter_combines_with_other_filters_using_and_logic()
    {
        $sportsCategory = Category::create(['name' => 'Sports', 'slug' => 'sports']);

        // Kids + Sports
        $kidsSports = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Junior Soccer Camp',
            'event_date' => now()->addDays(2),
            'start_time' => '09:00',
            'status' => 'published',
            'event_type' => 'free',
            'category_id' => $sportsCategory->id,
            'audience' => [Event::AUDIENCE_KIDS],
        ]);

        // Kids + Music
        $kidsMusic = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Kids Choir Performance',
            'event_date' => now()->addDays(2),
            'start_time' => '10:00',
            'status' => 'published',
            'event_type' => 'free',
            'category_id' => $this->category->id,
            'audience' => [Event::AUDIENCE_KIDS],
        ]);

        // Filter by Kids AND Sports category
        $response = $this->get(route('search', [
            'audience' => [Event::AUDIENCE_KIDS],
            'category_id' => $sportsCategory->id,
        ]));

        $response->assertOk();
        $response->assertSee($kidsSports->title);
        $response->assertDontSee($kidsMusic->title);
    }

    public function test_clearing_audience_filter_restores_all_results()
    {
        Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Event A Kids',
            'event_date' => now()->addDays(2),
            'start_time' => '10:00',
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_KIDS],
        ]);

        Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Event B Adults',
            'event_date' => now()->addDays(3),
            'start_time' => '20:00',
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_ADULTS],
        ]);

        // Unfiltered search
        $response = $this->get(route('search'));
        $response->assertOk();
        $response->assertSee('Event A Kids');
        $response->assertSee('Event B Adults');
    }

    public function test_empty_state_shown_when_no_audience_matches()
    {
        Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Adults Exclusive Gala',
            'event_date' => now()->addDays(2),
            'start_time' => '20:00',
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_ADULTS],
        ]);

        // Filter by Kids (no events match)
        $response = $this->get(route('search', ['audience' => [Event::AUDIENCE_KIDS]]));
        $response->assertOk();
        $response->assertSee(__('No events found'));
        $response->assertDontSee('Adults Exclusive Gala');
    }

    public function test_json_api_response_includes_audience()
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Youth Science Fair',
            'event_date' => now()->addDays(2),
            'start_time' => '10:00',
            'status' => 'published',
            'event_type' => 'free',
            'audience' => [Event::AUDIENCE_TEENS, Event::AUDIENCE_ALL_AGES],
        ]);

        $response = $this->getJson(route('search', ['audience' => [Event::AUDIENCE_TEENS]]));
        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Youth Science Fair']);
    }
}
