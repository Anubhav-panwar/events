<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventMarketplaceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_vendor_can_create_update_publish_event_and_add_ticket_type(): void
    {
        $vendor = User::query()->firstWhere('email', 'vendor@example.com');
        $this->actingAs($vendor);

        $createResponse = $this->post(route('vendor.events.store'), [
            'title' => 'Laravel Meetup',
            'description' => 'Hands-on meetup',
            'venue_name' => 'Tech Hub',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'start_time' => '18:30',
            'end_time' => '20:30',
            'address' => '100 Main Street',
            'city' => 'San Francisco',
            'country' => 'USA',
            'latitude' => 37.7749,
            'longitude' => -122.4194,
            'category_id' => 1,
            'capacity' => 150,
            'status' => 'draft',
            'event_type' => 'paid',
            'base_price' => 15,
            'tags' => 'laravel, php',
            'audience' => 'developers, students',
            'is_featured' => 1,
        ]);

        $event = Event::query()->firstWhere('title', 'Laravel Meetup');

        $createResponse->assertRedirect(route('events.show', $event->slug));

        $updateResponse = $this->put(route('vendor.events.update', $event), [
            'title' => 'Laravel Meetup Updated',
            'description' => 'Updated details',
            'venue_name' => 'Tech Hub',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'start_time' => '18:30',
            'end_time' => '20:30',
            'address' => '100 Main Street',
            'city' => 'San Francisco',
            'country' => 'USA',
            'latitude' => 37.7749,
            'longitude' => -122.4194,
            'category_id' => 1,
            'capacity' => 150,
            'status' => 'draft',
            'event_type' => 'paid',
            'base_price' => 20,
            'tags' => 'laravel, php, api',
            'audience' => 'developers',
            'is_featured' => 1,
        ]);

        $updateResponse->assertRedirect(route('vendor.events.edit', $event));
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Laravel Meetup Updated',
            'status' => 'draft',
            'event_type' => 'paid',
        ]);

        $this->patch(route('vendor.events.publish', $event))->assertRedirect();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'status' => 'published',
        ]);

        $ticketResponse = $this->post(route('vendor.tickets.store', $event), [
            'name' => 'VIP',
            'price' => 49.99,
            'currency' => 'USD',
            'quantity' => 75,
            'sales_start' => now()->subDay()->toDateTimeString(),
            'sales_end' => now()->addDays(8)->toDateTimeString(),
        ]);

        $ticketResponse->assertRedirect(route('vendor.events.edit', $event));
        $this->assertDatabaseHas('ticket_types', [
            'event_id' => $event->id,
            'name' => 'VIP',
        ]);
    }

    public function test_user_can_register_free_event_receive_ticket_and_vendor_can_check_in(): void
    {
        $vendor = User::query()->firstWhere('email', 'vendor@example.com');
        $regularUser = User::query()->firstWhere('email', 'user@example.com');
        $profile = VendorProfile::query()->firstWhere('user_id', $vendor->id);

        $event = Event::query()->create([
            'vendor_profile_id' => $profile->id,
            'title' => 'Free Yoga Session',
            'description' => 'Morning yoga in park',
            'venue_name' => 'Central Park',
            'event_date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '08:00',
            'end_time' => '09:00',
            'address' => 'Park Avenue',
            'city' => 'San Francisco',
            'country' => 'USA',
            'status' => 'published',
            'event_type' => 'free',
            'base_price' => 0,
            'slug' => 'free-yoga-session',
        ]);

        $this->actingAs($regularUser)
            ->post(route('orders.buy', $event->slug), [
                'quantity' => 1,
            ])
            ->assertRedirect();

        $order = Order::query()->where('user_id', $regularUser->id)->where('event_id', $event->id)->first();
        $this->assertNotNull($order);
        $this->assertSame('paid', $order->status);

        $ticket = Ticket::query()
            ->whereHas('orderItem.order', fn ($q) => $q->where('id', $order->id))
            ->first();

        $this->assertNotNull($ticket);

        $this->actingAs($vendor)
            ->post(route('vendor.checkin.validate'), ['code' => $ticket->code])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
        ]);
        $this->assertNotNull($ticket->fresh()->checked_in_at);
    }

    public function test_referral_and_share_clicks_are_tracked_and_applied_to_order(): void
    {
        $referrer = User::query()->firstWhere('email', 'vendor@example.com');
        $buyer = User::query()->firstWhere('email', 'user@example.com');
        $profile = VendorProfile::query()->firstWhere('user_id', $referrer->id);

        $event = Event::query()->create([
            'vendor_profile_id' => $profile->id,
            'title' => 'Free Coding Camp',
            'description' => 'One day coding camp',
            'venue_name' => 'Innovation Center',
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '16:00',
            'address' => '200 Innovation Road',
            'city' => 'San Francisco',
            'country' => 'USA',
            'status' => 'published',
            'event_type' => 'free',
            'base_price' => 0,
            'slug' => 'free-coding-camp',
        ]);

        $this->actingAs($buyer)
            ->get(route('events.show', ['slug' => $event->slug, 'ref' => $referrer->id]))
            ->assertOk();

        $copyResponse = $this->actingAs($buyer)
            ->get(route('events.share', ['slug' => $event->slug, 'channel' => 'copy']));

        $copyResponse->assertOk();
        $copyResponse->assertJsonStructure(['url']);

        $this->assertDatabaseHas('event_share_clicks', [
            'event_id' => $event->id,
            'channel' => 'copy',
        ]);

        $this->actingAs($buyer)
            ->post(route('orders.buy', $event->slug), [
                'quantity' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $buyer->id,
            'event_id' => $event->id,
            'referred_by_user_id' => $referrer->id,
        ]);
    }

    public function test_paid_checkout_requires_stripe_secret_when_not_configured(): void
    {
        config()->set('services.stripe.secret', null);

        $user = User::query()->firstWhere('email', 'user@example.com');
        $event = Event::query()->firstWhere('slug', 'opening-night-demo');
        $ticketType = TicketType::query()->where('event_id', $event->id)->first();

        $this->actingAs($user)
            ->post(route('orders.buy', $event->slug), [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('buy');
    }
}
