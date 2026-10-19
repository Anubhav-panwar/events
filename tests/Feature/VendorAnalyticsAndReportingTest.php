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

class VendorAnalyticsAndReportingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_unauthenticated_user_cannot_access_vendor_analytics(): void
    {
        $response = $this->get(route('vendor.analytics.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_vendor_analytics(): void
    {
        $user = User::query()->firstWhere('email', 'user@example.com');
        $response = $this->actingAs($user)->get(route('vendor.analytics.index'));
        $response->assertForbidden();
    }

    public function test_vendor_analytics_displays_accurate_tickets_sold_and_revenue_for_events(): void
    {
        $vendorUser = User::query()->firstWhere('email', 'vendor@example.com');
        $vendor = VendorProfile::query()->firstWhere('user_id', $vendorUser->id);
        $buyer = User::query()->firstWhere('email', 'user@example.com');

        // Event A: Paid concert
        $eventA = Event::create([
            'vendor_profile_id' => $vendor->id,
            'title' => 'Indie Rock Fest',
            'slug' => 'indie-rock-fest',
            'venue_name' => 'Metro Arena',
            'city' => 'San Francisco',
            'address' => '500 Arena Way',
            'event_date' => now()->addDays(14)->toDateString(),
            'start_time' => '19:00',
            'capacity' => 200,
            'status' => 'published',
            'is_date_confirmed' => true,
            'event_type' => 'paid',
            'base_price' => 50,
        ]);

        $tierA = TicketType::create([
            'event_id' => $eventA->id,
            'name' => 'General Admission',
            'price' => 50.00,
            'currency' => 'USD',
            'quantity' => 200,
            'sold' => 3,
        ]);

        // Create paid order for Event A: 3 tickets @ $50 = $150
        $orderA = Order::create([
            'user_id' => $buyer->id,
            'vendor_profile_id' => $vendor->id,
            'event_id' => $eventA->id,
            'total' => 150.00,
            'platform_fee' => 7.50, // 5% fee
            'refunded_amount' => 0.00,
            'currency' => 'USD',
            'status' => 'paid',
            'payment_provider' => 'stripe',
            'paid_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $orderA->id,
            'ticket_type_id' => $tierA->id,
            'quantity' => 3,
            'unit_price' => 50.00,
        ]);

        // Event B: Free workshop
        $eventB = Event::create([
            'vendor_profile_id' => $vendor->id,
            'title' => 'Free Community Tech Talk',
            'slug' => 'free-community-tech-talk',
            'venue_name' => 'Public Library',
            'city' => 'San Francisco',
            'address' => '100 Library Lane',
            'event_date' => now()->addDays(20)->toDateString(),
            'start_time' => '14:00',
            'capacity' => 50,
            'status' => 'published',
            'is_date_confirmed' => true,
            'event_type' => 'free',
            'base_price' => 0,
        ]);

        $tierB = TicketType::create([
            'event_id' => $eventB->id,
            'name' => 'Free Pass',
            'price' => 0.00,
            'currency' => 'USD',
            'quantity' => 50,
            'sold' => 5,
        ]);

        $orderB = Order::create([
            'user_id' => $buyer->id,
            'vendor_profile_id' => $vendor->id,
            'event_id' => $eventB->id,
            'total' => 0.00,
            'platform_fee' => 0.00,
            'currency' => 'USD',
            'status' => 'paid',
            'payment_provider' => 'free',
            'paid_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $orderB->id,
            'ticket_type_id' => $tierB->id,
            'quantity' => 5,
            'unit_price' => 0.00,
        ]);

        $response = $this->actingAs($vendorUser)->get(route('vendor.analytics.index'));
        $response->assertOk();

        // Check overall calculations
        $response->assertSee('150.00'); // Gross revenue
        $response->assertSee('142.50'); // Net revenue ($150 - $7.50 platform fee)
        $response->assertSee('7.50');   // Platform fees
        $response->assertSee('8');      // 3 paid + 5 free tickets sold

        // Event A row details
        $response->assertSee('Indie Rock Fest');
        $response->assertSee('Free Community Tech Talk');
    }

    public function test_revenue_calculations_account_for_refunds_and_cancellations(): void
    {
        $vendorUser = User::query()->firstWhere('email', 'vendor@example.com');
        $vendor = VendorProfile::query()->firstWhere('user_id', $vendorUser->id);
        $buyer = User::query()->firstWhere('email', 'user@example.com');

        $event = Event::create([
            'vendor_profile_id' => $vendor->id,
            'title' => 'Spring Acoustic Night',
            'slug' => 'spring-acoustic-night',
            'venue_name' => 'The Loft',
            'event_date' => now()->addDays(7)->toDateString(),
            'start_time' => '20:00',
            'capacity' => 100,
            'status' => 'published',
            'is_date_confirmed' => true,
            'event_type' => 'paid',
            'base_price' => 50,
        ]);

        $tier = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Standard',
            'price' => 50.00,
            'currency' => 'USD',
            'quantity' => 100,
            'sold' => 4,
        ]);

        // Order 1: Paid ($100 gross, $5 platform fee)
        $paidOrder = Order::create([
            'user_id' => $buyer->id,
            'vendor_profile_id' => $vendor->id,
            'event_id' => $event->id,
            'total' => 100.00,
            'platform_fee' => 5.00,
            'refunded_amount' => 0.00,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $paidOrder->id,
            'ticket_type_id' => $tier->id,
            'quantity' => 2,
            'unit_price' => 50.00,
        ]);

        // Order 2: Refunded ($50 total refunded)
        $refundedOrder = Order::create([
            'user_id' => $buyer->id,
            'vendor_profile_id' => $vendor->id,
            'event_id' => $event->id,
            'total' => 50.00,
            'platform_fee' => 0.00,
            'refunded_amount' => 50.00,
            'currency' => 'USD',
            'status' => 'refunded',
            'paid_at' => now()->subDay(),
            'refunded_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $refundedOrder->id,
            'ticket_type_id' => $tier->id,
            'quantity' => 1,
            'unit_price' => 50.00,
        ]);

        // Order 3: Cancelled ($50 nominal value, 0 paid)
        $cancelledOrder = Order::create([
            'user_id' => $buyer->id,
            'vendor_profile_id' => $vendor->id,
            'event_id' => $event->id,
            'total' => 50.00,
            'platform_fee' => 0.00,
            'refunded_amount' => 0.00,
            'currency' => 'USD',
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $cancelledOrder->id,
            'ticket_type_id' => $tier->id,
            'quantity' => 1,
            'unit_price' => 50.00,
        ]);

        $response = $this->actingAs($vendorUser)->get(route('vendor.analytics.index'));
        $response->assertOk();

        // Gross = $100 (only paid orders count towards gross)
        $response->assertSee('100.00');
        // Refunded = $50.00
        $response->assertSee('50.00');
        // Platform fee = $5.00
        $response->assertSee('5.00');
        // Net Revenue = $100 - $50 - $5 = $45.00
        $response->assertSee('45.00');
    }

    public function test_ticket_category_wise_breakdown_displays_tier_sales_and_revenue(): void
    {
        $vendorUser = User::query()->firstWhere('email', 'vendor@example.com');
        $vendor = VendorProfile::query()->firstWhere('user_id', $vendorUser->id);
        $buyer = User::query()->firstWhere('email', 'user@example.com');

        $event = Event::create([
            'vendor_profile_id' => $vendor->id,
            'title' => 'VIP Gala Dinner',
            'slug' => 'vip-gala-dinner',
            'venue_name' => 'Grand Ballroom',
            'event_date' => now()->addDays(15)->toDateString(),
            'start_time' => '19:00',
            'capacity' => 100,
            'status' => 'published',
            'is_date_confirmed' => true,
            'event_type' => 'paid',
            'base_price' => 100,
        ]);

        $vipTier = TicketType::create([
            'event_id' => $event->id,
            'name' => 'VIP Diamond Access',
            'price' => 200.00,
            'currency' => 'USD',
            'quantity' => 25,
            'sold' => 2,
        ]);

        $standardTier = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Standard Admission',
            'price' => 80.00,
            'currency' => 'USD',
            'quantity' => 75,
            'sold' => 3,
        ]);

        // Order VIP: 2 tickets @ $200 = $400
        $vipOrder = Order::create([
            'user_id' => $buyer->id,
            'vendor_profile_id' => $vendor->id,
            'event_id' => $event->id,
            'total' => 400.00,
            'platform_fee' => 20.00,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $vipOrder->id,
            'ticket_type_id' => $vipTier->id,
            'quantity' => 2,
            'unit_price' => 200.00,
        ]);

        // Order Standard: 3 tickets @ $80 = $240
        $standardOrder = Order::create([
            'user_id' => $buyer->id,
            'vendor_profile_id' => $vendor->id,
            'event_id' => $event->id,
            'total' => 240.00,
            'platform_fee' => 12.00,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $standardOrder->id,
            'ticket_type_id' => $standardTier->id,
            'quantity' => 3,
            'unit_price' => 80.00,
        ]);

        $response = $this->actingAs($vendorUser)->get(route('vendor.analytics.index'));
        $response->assertOk();

        // VIP Tier displayed
        $response->assertSee('VIP Diamond Access');
        $response->assertSee('400.00'); // VIP Gross
        $response->assertSee('380.00'); // VIP Net ($400 - $20 fee)

        // Standard Tier displayed
        $response->assertSee('Standard Admission');
        $response->assertSee('240.00'); // Standard Gross
        $response->assertSee('228.00'); // Standard Net ($240 - $12 fee)
    }

    public function test_date_wise_filtering_and_event_filtering_work_correctly(): void
    {
        $vendorUser = User::query()->firstWhere('email', 'vendor@example.com');
        $vendor = VendorProfile::query()->firstWhere('user_id', $vendorUser->id);
        $buyer = User::query()->firstWhere('email', 'user@example.com');

        $event = Event::create([
            'vendor_profile_id' => $vendor->id,
            'title' => 'Jazz at Midnight',
            'slug' => 'jazz-at-midnight',
            'venue_name' => 'Blue Note',
            'event_date' => now()->addDays(5)->toDateString(),
            'start_time' => '22:00',
            'capacity' => 100,
            'status' => 'published',
            'is_date_confirmed' => true,
            'event_type' => 'paid',
            'base_price' => 30,
        ]);

        $tier = TicketType::create([
            'event_id' => $event->id,
            'name' => 'General',
            'price' => 30.00,
            'currency' => 'USD',
            'quantity' => 100,
        ]);

        // Today's order: $30
        $todayOrder = Order::create([
            'user_id' => $buyer->id,
            'vendor_profile_id' => $vendor->id,
            'event_id' => $event->id,
            'total' => 30.00,
            'platform_fee' => 1.50,
            'currency' => 'USD',
            'status' => 'paid',
            'created_at' => now(),
            'paid_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $todayOrder->id,
            'ticket_type_id' => $tier->id,
            'quantity' => 1,
            'unit_price' => 30.00,
        ]);

        // 45 days ago order: $60
        $oldOrder = Order::withoutTimestamps(function () use ($buyer, $vendor, $event) {
            return Order::create([
                'user_id' => $buyer->id,
                'vendor_profile_id' => $vendor->id,
                'event_id' => $event->id,
                'total' => 60.00,
                'platform_fee' => 3.00,
                'currency' => 'USD',
                'status' => 'paid',
                'created_at' => now()->subDays(45),
                'paid_at' => now()->subDays(45),
            ]);
        });
        OrderItem::create([
            'order_id' => $oldOrder->id,
            'ticket_type_id' => $tier->id,
            'quantity' => 2,
            'unit_price' => 30.00,
        ]);

        // Filter: last_7_days (should only show today's order: $30 gross, $28.50 net)
        $response7Days = $this->actingAs($vendorUser)->get(route('vendor.analytics.index', ['date_range' => 'last_7_days']));
        $response7Days->assertOk();
        $response7Days->assertSee('30.00');
        $response7Days->assertDontSee('90.00');

        // Filter: event_id
        $responseEvent = $this->actingAs($vendorUser)->get(route('vendor.analytics.index', ['event_id' => $event->id]));
        $responseEvent->assertOk();
        $responseEvent->assertSee('Jazz at Midnight');
    }

    public function test_csv_export_options_return_valid_csv_downloads(): void
    {
        $vendorUser = User::query()->firstWhere('email', 'vendor@example.com');
        $vendor = VendorProfile::query()->firstWhere('user_id', $vendorUser->id);
        $buyer = User::query()->firstWhere('email', 'user@example.com');

        $event = Event::create([
            'vendor_profile_id' => $vendor->id,
            'title' => 'Exportable Concert Showcase',
            'slug' => 'exportable-concert-showcase',
            'venue_name' => 'Convention Hall',
            'event_date' => now()->addDays(10)->toDateString(),
            'start_time' => '18:00',
            'capacity' => 150,
            'status' => 'published',
            'is_date_confirmed' => true,
            'event_type' => 'paid',
            'base_price' => 40,
        ]);

        $tier = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Main Tier',
            'price' => 40.00,
            'currency' => 'USD',
            'quantity' => 150,
            'sold' => 2,
        ]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'vendor_profile_id' => $vendor->id,
            'event_id' => $event->id,
            'total' => 80.00,
            'platform_fee' => 4.00,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'ticket_type_id' => $tier->id,
            'quantity' => 2,
            'unit_price' => 40.00,
        ]);

        // 1. Summary CSV
        $summaryResponse = $this->actingAs($vendorUser)->get(route('vendor.analytics.export', ['type' => 'summary']));
        $summaryResponse->assertOk();
        $summaryResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('event_sales_summary', $summaryResponse->headers->get('Content-Disposition') ?: '');

        // 2. Orders CSV
        $ordersResponse = $this->actingAs($vendorUser)->get(route('vendor.analytics.export', ['type' => 'orders']));
        $ordersResponse->assertOk();
        $ordersResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // 3. Categories CSV
        $categoriesResponse = $this->actingAs($vendorUser)->get(route('vendor.analytics.export', ['type' => 'categories']));
        $categoriesResponse->assertOk();
        $categoriesResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_vendor_cannot_view_another_vendors_events_in_analytics(): void
    {
        $vendorUserA = User::query()->firstWhere('email', 'vendor@example.com');
        $vendorA = VendorProfile::query()->firstWhere('user_id', $vendorUserA->id);

        // Create Vendor B
        $vendorUserB = User::create([
            'name' => 'Vendor B',
            'email' => 'vendor_b@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
        ]);
        $vendorB = VendorProfile::create([
            'user_id' => $vendorUserB->id,
            'business_name' => 'Vendor B Enterprise',
            'slug' => 'vendor-b-enterprise',
            'is_approved' => true,
        ]);

        // Event for Vendor B
        Event::create([
            'vendor_profile_id' => $vendorB->id,
            'title' => 'Secret Vendor B Gala',
            'slug' => 'secret-vendor-b-gala',
            'venue_name' => 'Private Villa',
            'event_date' => now()->addDays(12)->toDateString(),
            'start_time' => '19:00',
            'capacity' => 100,
            'status' => 'published',
            'is_date_confirmed' => true,
            'event_type' => 'paid',
            'base_price' => 100,
        ]);

        // When Vendor A visits analytics, they must NOT see Vendor B's event
        $response = $this->actingAs($vendorUserA)->get(route('vendor.analytics.index'));
        $response->assertOk();
        $response->assertDontSee('Secret Vendor B Gala');
    }

    public function test_multilingual_analytics_page_renders_in_german_and_italian(): void
    {
        $vendorUser = User::query()->firstWhere('email', 'vendor@example.com');

        // German
        $responseDe = $this->actingAs($vendorUser)->get(route('vendor.analytics.index', ['lang' => 'de']));
        $responseDe->assertOk();
        $responseDe->assertSee('Event-Berichte & Analysen');
        $responseDe->assertSee('Bruttoumsatz');
        $responseDe->assertSee('Nettoumsatz');

        // Italian
        $responseIt = $this->actingAs($vendorUser)->get(route('vendor.analytics.index', ['lang' => 'it']));
        $responseIt->assertOk();
        $responseIt->assertSee('Report e Analisi Eventi');
        $responseIt->assertSee('Ricavi lordi');
        $responseIt->assertSee('Ricavi netti');
    }
}
