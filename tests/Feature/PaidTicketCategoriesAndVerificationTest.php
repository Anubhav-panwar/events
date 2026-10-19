<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Models\VendorProfile;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaidTicketCategoriesAndVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendorUser;
    protected VendorProfile $vendorProfile;
    protected Event $event;
    protected User $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendorUser = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Apex Productions',
            'email' => 'apex@example.com',
        ]);

        $this->vendorProfile = VendorProfile::create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'Apex Arena Productions',
            'slug' => 'apex-arena',
            'city' => 'San Francisco',
            'country' => 'USA',
        ]);

        $this->event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Global Tech & AI Summit 2026',
            'slug' => 'global-tech-ai-summit-2026',
            'description' => 'Premier technology and artificial intelligence conference.',
            'event_type' => 'paid',
            'status' => 'published',
            'published_at' => now(),
            'event_date' => now()->addMonth(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'capacity' => 1000,
        ]);

        $this->customerUser = User::factory()->create([
            'role' => 'user',
            'name' => 'Jane Techie',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_vendor_can_create_bronze_silver_gold_diamond_ticket_categories(): void
    {
        $tiers = [
            ['name' => 'Bronze Pass', 'tier' => 'bronze', 'price' => 49.00, 'quantity' => 100, 'description' => 'General conference seating'],
            ['name' => 'Silver Pass', 'tier' => 'silver', 'price' => 99.00, 'quantity' => 50, 'description' => 'Priority seating & workshop access'],
            ['name' => 'Gold Pass', 'tier' => 'gold', 'price' => 199.00, 'quantity' => 25, 'description' => 'VIP seating & networking dinner'],
            ['name' => 'Diamond Pass', 'tier' => 'diamond', 'price' => 499.00, 'quantity' => 10, 'description' => 'All-inclusive VIP backstage pass'],
        ];

        foreach ($tiers as $tierData) {
            $response = $this->actingAs($this->vendorUser)
                ->post(route('vendor.tickets.store', $this->event), [
                    'name' => $tierData['name'],
                    'tier' => $tierData['tier'],
                    'price' => $tierData['price'],
                    'quantity' => $tierData['quantity'],
                    'description' => $tierData['description'],
                    'is_active' => '1',
                ]);

            $response->assertRedirect(route('vendor.events.edit', $this->event));
            $this->assertDatabaseHas('ticket_types', [
                'event_id' => $this->event->id,
                'name' => $tierData['name'],
                'tier' => $tierData['tier'],
                'price' => $tierData['price'],
                'quantity' => $tierData['quantity'],
                'is_active' => true,
            ]);
        }
    }

    public function test_vendor_can_generate_all_default_tiers_with_one_click(): void
    {
        $response = $this->actingAs($this->vendorUser)
            ->post(route('vendor.tickets.generate_defaults', $this->event));

        $response->assertRedirect(route('vendor.events.edit', $this->event));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('ticket_types', ['event_id' => $this->event->id, 'tier' => 'bronze', 'name' => 'Bronze Admission']);
        $this->assertDatabaseHas('ticket_types', ['event_id' => $this->event->id, 'tier' => 'silver', 'name' => 'Silver Pass']);
        $this->assertDatabaseHas('ticket_types', ['event_id' => $this->event->id, 'tier' => 'gold', 'name' => 'Gold VIP']);
        $this->assertDatabaseHas('ticket_types', ['event_id' => $this->event->id, 'tier' => 'diamond', 'name' => 'Diamond Elite']);
        $this->assertEquals(4, $this->event->ticketTypes()->count());
    }

    public function test_vendor_can_edit_and_manage_ticket_category_pricing_and_availability(): void
    {
        $ticketType = TicketType::create([
            'event_id' => $this->event->id,
            'name' => 'Early Bird Silver',
            'tier' => 'silver',
            'price' => 75.00,
            'quantity' => 40,
            'sold' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->vendorUser)
            ->put(route('vendor.tickets.update', [$this->event, $ticketType]), [
                'name' => 'Updated Silver Tier',
                'tier' => 'silver',
                'price' => 85.00,
                'quantity' => 60,
                'description' => 'Updated silver perks with lunch',
                // is_active omitted = toggled inactive
            ]);

        $response->assertRedirect(route('vendor.events.edit', $this->event));
        $ticketType->refresh();
        $this->assertEquals('Updated Silver Tier', $ticketType->name);
        $this->assertEquals(85.00, (float) $ticketType->price);
        $this->assertEquals(60, $ticketType->quantity);
        $this->assertFalse($ticketType->is_active);
    }

    public function test_deleting_ticket_category_with_sold_tickets_safely_deactivates_it(): void
    {
        $ticketType = TicketType::create([
            'event_id' => $this->event->id,
            'name' => 'Gold VIP',
            'tier' => 'gold',
            'price' => 150.00,
            'quantity' => 20,
            'sold' => 5, // has sales
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->vendorUser)
            ->delete(route('vendor.tickets.destroy', [$this->event, $ticketType]));

        $response->assertRedirect(route('vendor.events.edit', $this->event));
        $this->assertDatabaseHas('ticket_types', [
            'id' => $ticketType->id,
            'is_active' => false,
        ]);
    }

    public function test_display_available_ticket_categories_and_prices_to_users(): void
    {
        TicketType::create([
            'event_id' => $this->event->id,
            'name' => 'Bronze Admission',
            'tier' => 'bronze',
            'price' => 25.00,
            'quantity' => 100,
            'sold' => 10,
            'is_active' => true,
        ]);

        TicketType::create([
            'event_id' => $this->event->id,
            'name' => 'Diamond Elite',
            'tier' => 'diamond',
            'price' => 200.00,
            'quantity' => 10,
            'sold' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->get(route('events.show', $this->event->slug));

        $response->assertStatus(200);
        $response->assertSee('Bronze Admission');
        $response->assertSee('Diamond Elite');
        $response->assertSee('Available Ticket Categories');
    }

    public function test_ticket_codes_are_generated_only_after_successful_payment_verification(): void
    {
        $ticketType = TicketType::create([
            'event_id' => $this->event->id,
            'name' => 'Gold Pass',
            'tier' => 'gold',
            'price' => 120.00,
            'quantity' => 50,
            'sold' => 0,
            'is_active' => true,
        ]);

        $checkoutService = app(CheckoutService::class);

        // 1. Pending order (payment not yet confirmed)
        $pendingOrder = $checkoutService->createOrder(
            user: $this->customerUser,
            event: $this->event,
            ticketTypeId: $ticketType->id,
            quantity: 2,
            paymentProvider: 'stripe',
            status: 'pending'
        );

        $this->assertEquals('pending', $pendingOrder->status);
        // Verify ZERO tickets issued while payment is pending
        $this->assertEquals(0, Ticket::query()->whereHas('orderItem', fn($q) => $q->where('order_id', $pendingOrder->id))->count());

        // 2. Mark order paid (simulating verified payment webhook)
        $paidOrder = $checkoutService->markPaid($pendingOrder, 'pi_test_successful_123');
        $this->assertEquals('paid', $paidOrder->status);

        // Verify tickets issued ONLY now
        $tickets = Ticket::query()->whereHas('orderItem', fn($q) => $q->where('order_id', $paidOrder->id))->get();
        $this->assertCount(2, $tickets);

        foreach ($tickets as $ticket) {
            $this->assertNotNull($ticket->code);
            // Format check: TKT-EV{id}-G-{random8}
            $this->assertStringStartsWith("TKT-EV{$this->event->id}-G-", $ticket->code);
            $this->assertNotNull($ticket->issued_at);
            $this->assertNull($ticket->checked_in_at);
        }

        // Codes must be distinct
        $this->assertNotEquals($tickets[0]->code, $tickets[1]->code);
    }

    public function test_booking_confirmation_and_ticket_details_pages_display_ticket_codes(): void
    {
        $ticketType = TicketType::create([
            'event_id' => $this->event->id,
            'name' => 'Diamond Pass',
            'tier' => 'diamond',
            'price' => 300.00,
            'quantity' => 20,
            'sold' => 0,
            'is_active' => true,
        ]);

        $checkoutService = app(CheckoutService::class);
        $order = $checkoutService->createOrder(
            user: $this->customerUser,
            event: $this->event,
            ticketTypeId: $ticketType->id,
            quantity: 1,
            paymentProvider: 'stripe',
            status: 'pending'
        );
        $order = $checkoutService->markPaid($order, 'pi_confirmed_999');

        $ticket = Ticket::query()->whereHas('orderItem', fn($q) => $q->where('order_id', $order->id))->firstOrFail();

        // 1. Booking confirmation page (checkout.success)
        $confirmationResponse = $this->actingAs($this->customerUser)
            ->get(route('checkout.success', ['order' => $order->id]));

        $confirmationResponse->assertStatus(200);
        $confirmationResponse->assertSee($ticket->code);
        $confirmationResponse->assertSee('Issued Ticket Codes');
        $confirmationResponse->assertSee('Diamond Pass');

        // 2. Ticket details page (account.tickets.show)
        $ticketDetailsResponse = $this->actingAs($this->customerUser)
            ->get(route('account.tickets.show', $ticket));

        $ticketDetailsResponse->assertStatus(200);
        $ticketDetailsResponse->assertSee($ticket->code);
        $ticketDetailsResponse->assertSee('Unique Ticket Code');
        $ticketDetailsResponse->assertSee('Diamond Pass');
    }

    public function test_ticket_verification_and_anti_reuse_protection(): void
    {
        $ticketType = TicketType::create([
            'event_id' => $this->event->id,
            'name' => 'Bronze Pass',
            'tier' => 'bronze',
            'price' => 30.00,
            'quantity' => 10,
            'sold' => 0,
            'is_active' => true,
        ]);

        $checkoutService = app(CheckoutService::class);
        $order = $checkoutService->createOrder(
            user: $this->customerUser,
            event: $this->event,
            ticketTypeId: $ticketType->id,
            quantity: 1,
            paymentProvider: 'stripe',
            status: 'pending'
        );
        $order = $checkoutService->markPaid($order, 'pi_verify_test');

        $ticket = Ticket::query()->whereHas('orderItem', fn($q) => $q->where('order_id', $order->id))->firstOrFail();

        // 1. Verify-only mode does not consume admission
        $verifyResponse = $this->actingAs($this->vendorUser)
            ->post(route('vendor.checkin.validate'), [
                'code' => $ticket->code,
                'mode' => 'verify',
            ]);

        $verifyResponse->assertSessionHas('status');
        $this->assertNull($ticket->fresh()->checked_in_at);

        // 2. First check-in admits attendee
        $checkinResponse = $this->actingAs($this->vendorUser)
            ->post(route('vendor.checkin.validate'), [
                'code' => $ticket->code,
                'mode' => 'checkin',
            ]);

        $checkinResponse->assertSessionHas('status', 'Ticket valid. Checked in.');
        $this->assertNotNull($ticket->fresh()->checked_in_at);

        // 3. Duplicate scan attempt is rejected (Anti-reuse safeguard)
        $duplicateResponse = $this->actingAs($this->vendorUser)
            ->post(route('vendor.checkin.validate'), [
                'code' => $ticket->code,
                'mode' => 'checkin',
            ]);

        $duplicateResponse->assertSessionHasErrors('code');
        $duplicateResponse->assertSessionHas('scanned_result');
        $scannedResult = session('scanned_result');
        $this->assertFalse($scannedResult['valid']);
        $this->assertEquals('already_used', $scannedResult['type']);
    }

    public function test_ticket_verification_rejects_unpaid_or_refunded_orders(): void
    {
        $ticketType = TicketType::create([
            'event_id' => $this->event->id,
            'name' => 'Silver Tier',
            'tier' => 'silver',
            'price' => 60.00,
            'quantity' => 10,
            'sold' => 1,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $this->customerUser->id,
            'vendor_profile_id' => $this->vendorProfile->id,
            'event_id' => $this->event->id,
            'total' => 60.00,
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 1,
            'unit_price' => 60.00,
        ]);

        $ticket = Ticket::create([
            'order_item_id' => $item->id,
            'code' => Ticket::generateUniqueCode($this->event->id, 'silver'),
            'attendee_name' => 'Jane Techie',
            'issued_at' => now(),
        ]);

        $response = $this->actingAs($this->vendorUser)
            ->post(route('vendor.checkin.validate'), [
                'code' => $ticket->code,
            ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull($ticket->fresh()->checked_in_at);
    }
}
