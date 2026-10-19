<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventReminder;
use App\Models\EventReview;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Models\UserBlockRecord;
use App\Models\VendorProfile;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdminUserBlockingAndAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $customerUser;
    protected User $vendorUser;
    protected VendorProfile $vendorProfile;
    protected Event $event;
    protected TicketType $ticketType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'name' => 'Master Administrator',
            'email' => 'admin@example.com',
            'is_blocked' => false,
        ]);

        $this->customerUser = User::factory()->create([
            'role' => 'user',
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'is_blocked' => false,
        ]);

        $this->vendorUser = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Solaris Events Vendor',
            'email' => 'vendor@example.com',
            'is_blocked' => false,
        ]);

        $this->vendorProfile = VendorProfile::create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'Solaris Global Events',
            'slug' => 'solaris-global-events',
            'city' => 'Miami',
            'country' => 'USA',
        ]);

        $this->event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'title' => 'Electric Forest Rave',
            'slug' => 'electric-forest-rave',
            'description' => 'Electronic music festival in the woods.',
            'event_type' => 'paid',
            'status' => 'published',
            'published_at' => now(),
            'event_date' => now()->addMonth(),
            'start_time' => '20:00',
            'end_time' => '04:00',
            'capacity' => 500,
        ]);

        $this->ticketType = TicketType::create([
            'event_id' => $this->event->id,
            'name' => 'General Admission',
            'tier' => 'bronze',
            'price' => 50.00,
            'quantity' => 100,
            'quantity_sold' => 0,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_block_a_user_with_reason_and_audit_record_created(): void
    {
        // Add a reminder first to verify it gets muted/deleted upon blocking
        EventReminder::create([
            'user_id' => $this->customerUser->id,
            'event_id' => $this->event->id,
            'remind_at' => now()->addDays(5),
            'channel' => 'email',
        ]);

        $this->assertDatabaseHas('event_reminders', [
            'user_id' => $this->customerUser->id,
            'event_id' => $this->event->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.users.block', $this->customerUser), [
                'reason' => 'Violated community guidelines with spam reviews and fraudulent booking attempts.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check user table updated
        $this->customerUser->refresh();
        $this->assertTrue($this->customerUser->is_blocked);
        $this->assertNotNull($this->customerUser->blocked_at);
        $this->assertEquals('Violated community guidelines with spam reviews and fraudulent booking attempts.', $this->customerUser->blocked_reason);
        $this->assertEquals($this->adminUser->id, $this->customerUser->blocked_by_id);

        // Check audit record created
        $this->assertDatabaseHas('user_block_records', [
            'user_id' => $this->customerUser->id,
            'admin_id' => $this->adminUser->id,
            'action' => 'blocked',
            'reason' => 'Violated community guidelines with spam reviews and fraudulent booking attempts.',
        ]);

        // Check reminders muted/deleted
        $this->assertDatabaseMissing('event_reminders', [
            'user_id' => $this->customerUser->id,
        ]);
    }

    public function test_admin_cannot_block_their_own_account(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.users.block', $this->adminUser), [
                'reason' => 'Self lock attempt',
            ]);

        $response->assertSessionHas('error');
        $this->adminUser->refresh();
        $this->assertFalse($this->adminUser->is_blocked);
    }

    public function test_admin_can_unblock_a_user_and_audit_record_created(): void
    {
        // First block the user
        $this->customerUser->update([
            'is_blocked' => true,
            'blocked_at' => now()->subDay(),
            'blocked_reason' => 'Temporary suspension pending KYC check.',
            'blocked_by_id' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.users.unblock', $this->customerUser), [
                'reason' => 'Identity verified and dispute resolved.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->customerUser->refresh();
        $this->assertFalse($this->customerUser->is_blocked);
        $this->assertNull($this->customerUser->blocked_at);
        $this->assertNull($this->customerUser->blocked_reason);
        $this->assertNull($this->customerUser->blocked_by_id);

        // Check audit log contains unblock entry
        $this->assertDatabaseHas('user_block_records', [
            'user_id' => $this->customerUser->id,
            'admin_id' => $this->adminUser->id,
            'action' => 'unblocked',
            'reason' => 'Identity verified and dispute resolved.',
        ]);
    }

    public function test_admin_can_fetch_block_history_audit_trail_json(): void
    {
        UserBlockRecord::create([
            'user_id' => $this->customerUser->id,
            'admin_id' => $this->adminUser->id,
            'action' => 'blocked',
            'reason' => 'Repeated chargeback warnings.',
            'ip_address' => '127.0.0.1',
        ]);

        UserBlockRecord::create([
            'user_id' => $this->customerUser->id,
            'admin_id' => $this->adminUser->id,
            'action' => 'unblocked',
            'reason' => 'Payment cleared via bank transfer.',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.users.block_history', $this->customerUser));

        $response->assertOk();
        $response->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'is_blocked'],
            'records' => [
                '*' => ['id', 'action', 'reason', 'admin_name', 'created_at'],
            ],
        ]);
        $response->assertJsonFragment(['action' => 'blocked', 'reason' => 'Repeated chargeback warnings.']);
        $response->assertJsonFragment(['action' => 'unblocked', 'reason' => 'Payment cleared via bank transfer.']);
    }

    public function test_blocked_user_cannot_log_in(): void
    {
        $password = 'Secret123!';
        $user = User::factory()->create([
            'email' => 'blocked.login@example.com',
            'password' => bcrypt($password),
            'is_blocked' => true,
            'blocked_reason' => 'Fraud investigation in progress.',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'blocked.login@example.com',
            'password' => $password,
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_blocked_user_session_is_terminated_by_middleware(): void
    {
        $this->customerUser->update([
            'is_blocked' => true,
            'blocked_reason' => 'Suspicious account takeover activity.',
        ]);

        $response = $this->actingAs($this->customerUser)
            ->get(route('dashboard'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_blocked_user_cannot_purchase_tickets(): void
    {
        $this->customerUser->update([
            'is_blocked' => true,
            'blocked_reason' => 'Purchase privileges revoked.',
        ]);

        $checkoutService = app(CheckoutService::class);

        $this->expectException(ValidationException::class);
        $checkoutService->createOrder(
            $this->customerUser,
            $this->event,
            $this->ticketType->id,
            2
        );
    }

    public function test_blocked_user_cannot_post_event_reviews(): void
    {
        $this->customerUser->update([
            'is_blocked' => true,
            'blocked_reason' => 'Account banned from posting reviews.',
        ]);

        // API/JSON request returns 403 Forbidden with blocked message
        $responseJson = $this->actingAs($this->customerUser)
            ->postJson(route('events.reviews.store', $this->event), [
                'rating' => 5,
                'comment' => 'This was an amazing experience!',
            ]);

        $responseJson->assertForbidden();
        $responseJson->assertJsonFragment(['blocked' => true]);

        // Web request redirects to login and invalidates session
        $responseWeb = $this->actingAs($this->customerUser)
            ->post(route('events.reviews.store', $this->event), [
                'rating' => 5,
                'comment' => 'Another comment attempt',
            ]);

        $responseWeb->assertRedirect(route('login'));
        $this->assertDatabaseMissing('event_reviews', [
            'user_id' => $this->customerUser->id,
            'event_id' => $this->event->id,
        ]);
    }

    public function test_blocked_user_cannot_set_reminders(): void
    {
        $this->customerUser->update([
            'is_blocked' => true,
            'blocked_reason' => 'Notification privileges suspended.',
        ]);

        // API/JSON request returns 403 Forbidden
        $responseJson = $this->actingAs($this->customerUser)
            ->postJson(route('events.reminders.store', $this->event), [
                'days_before' => 3,
                'channel' => 'email',
            ]);

        $responseJson->assertForbidden();
        $responseJson->assertJsonFragment(['blocked' => true]);

        // Web request redirects to login
        $responseWeb = $this->actingAs($this->customerUser)
            ->post(route('events.reminders.store', $this->event), [
                'days_before' => 3,
                'channel' => 'email',
            ]);

        $responseWeb->assertRedirect(route('login'));
        $this->assertDatabaseMissing('event_reminders', [
            'user_id' => $this->customerUser->id,
            'event_id' => $this->event->id,
        ]);
    }

    public function test_existing_bookings_are_preserved_when_user_is_blocked(): void
    {
        // 1. User has an existing valid order and ticket created via CheckoutService
        $checkoutService = app(CheckoutService::class);
        $order = $checkoutService->createOrder(
            user: $this->customerUser,
            event: $this->event,
            ticketTypeId: $this->ticketType->id,
            quantity: 1,
            paymentProvider: 'stripe',
            status: 'paid'
        );

        $tickets = Ticket::query()->whereHas('orderItem', fn($q) => $q->where('order_id', $order->id))->get();
        $this->assertCount(1, $tickets);
        $ticket = $tickets->first();
        $this->assertNotNull($ticket->code);

        // 2. Admin blocks user
        $this->actingAs($this->adminUser)
            ->post(route('admin.users.block', $this->customerUser), [
                'reason' => 'Compliance review',
            ]);

        // 3. Verify order and ticket are preserved in database for financial/audit integrity
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $this->customerUser->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'code' => $ticket->code,
        ]);
    }

    public function test_admin_dashboard_displays_account_status_and_filters(): void
    {
        $this->customerUser->update([
            'is_blocked' => true,
            'blocked_at' => now(),
            'blocked_reason' => 'Payment abuse violation.',
            'blocked_by_id' => $this->adminUser->id,
        ]);

        // Test filter active only
        $responseActive = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard', ['tab' => 'users', 'account_status' => 'active']));

        $responseActive->assertOk();
        $responseActive->assertSee($this->adminUser->name);
        $responseActive->assertDontSee('Payment abuse violation.');

        // Test filter blocked only
        $responseBlocked = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard', ['tab' => 'users', 'account_status' => 'blocked']));

        $responseBlocked->assertOk();
        $responseBlocked->assertSee($this->customerUser->name);
        $responseBlocked->assertSee('Payment abuse violation.');
    }
}
