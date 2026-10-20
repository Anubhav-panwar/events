<?php

namespace Tests\Feature;

use App\Mail\EventDatesAnnouncedMail;
use App\Jobs\SendEventReminderEmail;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventReminder;
use App\Models\TicketType;
use App\Models\User;
use App\Models\VendorProfile;
use App\Services\EventNotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class UpcomingEventsAndNotifyMeTest extends TestCase
{
    use RefreshDatabase;

    private User $vendorUser;
    private VendorProfile $vendorProfile;
    private Category $category;
    private User $customerUser;
    private User $customerUser2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendorUser = User::factory()->create([
            'role' => 'vendor',
            'email_verified_at' => now(),
        ]);

        $this->vendorProfile = VendorProfile::create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'Solaris Event Productions',
            'slug' => 'solaris-event-productions',
            'description' => 'World class festivals and upcoming mega-events.',
            'city' => 'San Francisco',
            'country' => 'United States',
            'address' => '500 Howard St',
            'is_approved' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Music & Festivals',
            'slug' => 'music-festivals',
        ]);

        $this->customerUser = User::factory()->create([
            'role' => 'user',
            'email' => 'alice@example.com',
            'email_verified_at' => now(),
        ]);

        $this->customerUser2 = User::factory()->create([
            'role' => 'user',
            'email' => 'bob@example.com',
            'email_verified_at' => now(),
        ]);
    }

    /** 1. Vendor saves an event as a draft without dates */
    public function test_vendor_can_save_event_as_draft_without_dates(): void
    {
        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), [
            'title' => 'Neon Horizon Festival 2027',
            'category_id' => $this->category->id,
            'status' => 'draft',
            'event_type' => '',
            'base_price' => '',
            'event_date' => '',
            'start_time' => '',
            'is_date_confirmed' => '0',
            'description' => 'Upcoming futuristic festival teaser.',
            'city' => 'San Francisco',
            'audience' => ['all_ages'],
        ]);

        $response->assertRedirect();

        $event = Event::where('title', 'Neon Horizon Festival 2027')->first();
        $this->assertNotNull($event);
        $this->assertEquals('draft', $event->status);
        $this->assertNull($event->event_date);
        $this->assertNull($event->start_time);
        $this->assertFalse((bool) $event->is_date_confirmed);
        $this->assertTrue($event->isDatesTba());
        $this->assertFalse($event->isBookingEligible());
    }

    /** 2. Vendor publishes an event without confirmed dates or pricing */
    public function test_vendor_can_publish_upcoming_event_with_unconfirmed_dates_and_pricing(): void
    {
        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), [
            'title' => 'Quantum Tech Summit 2027',
            'category_id' => $this->category->id,
            'status' => 'published',
            'event_type' => '',
            'base_price' => '',
            'event_date' => '',
            'start_time' => '',
            'is_date_confirmed' => '0',
            'description' => 'Annual global summit on quantum breakthroughs.',
            'city' => 'San Francisco',
            'audience' => ['adults'],
        ]);

        $response->assertRedirect();

        $event = Event::where('title', 'Quantum Tech Summit 2027')->first();
        $this->assertNotNull($event);
        $this->assertEquals('published', $event->status);
        $this->assertNull($event->event_date);
        $this->assertTrue($event->isDatesTba());
        $this->assertEquals('Published – Dates To Be Announced', $event->publication_status_label);
        $this->assertEquals('Booking: Not Available', $event->booking_status_label);
    }

    /** 3. Customer can see the event in Upcoming Events listing with TBA labels */
    public function test_customer_can_see_upcoming_tba_events_in_listings(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Aurora Soundwaves',
            'slug' => 'aurora-soundwaves',
            'description' => 'A celestial immersive audio journey.',
            'status' => 'published',
            'event_date' => null,
            'start_time' => null,
            'is_date_confirmed' => false,
            'event_type' => null,
            'base_price' => null,
            'city' => 'San Francisco',
        ]);

        // Home page listing
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Aurora Soundwaves');
        $homeResponse->assertSee('Date: To Be Announced');
        $homeResponse->assertSee('Price: TBA');

        // Event detail page
        $showResponse = $this->get(route('events.show', $event->slug));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Aurora Soundwaves');
        $showResponse->assertSee('Date: To Be Announced');
        $showResponse->assertSee('Ticket Price: To Be Announced');
        $showResponse->assertSee('Booking: Not Yet Available');
        $showResponse->assertSee('Notify Me');
    }

    /** 4. Customer cannot book tickets or add undated event to calendar */
    public function test_booking_and_calendar_remain_strictly_disabled_for_tba_events(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Undated Mystery Gala',
            'slug' => 'undated-mystery-gala',
            'description' => 'Exclusive annual gala.',
            'status' => 'published',
            'event_date' => null,
            'start_time' => null,
            'is_date_confirmed' => false,
            'event_type' => null,
            'base_price' => null,
        ]);

        // Calendar request (.ics) must be rejected with 422
        $icsResponse = $this->get(route('events.calendar.ics', $event->slug));
        $icsResponse->assertStatus(422);

        // Checkout session creation must be rejected with 422 validation exception
        $checkoutResponse = $this->actingAs($this->customerUser)->postJson(route('checkout.session', $event->slug), [
            'quantity' => 1,
        ]);
        $checkoutResponse->assertStatus(422);
        $this->assertFalse($event->isCalendarEligible());
        $this->assertFalse($event->isBookingEligible());
    }

    /** 5. Customer enables reminder and receives confirmation message */
    public function test_customer_can_subscribe_to_notify_me_reminder(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Horizon Eclipse',
            'slug' => 'horizon-eclipse',
            'description' => 'Solar eclipse viewing event.',
            'status' => 'published',
            'event_date' => null,
            'start_time' => null,
            'is_date_confirmed' => false,
            'event_type' => null,
        ]);

        $response = $this->actingAs($this->customerUser)->post(route('events.reminders.store', $event->slug));

        $response->assertSessionHas('status', "You're subscribed! We'll email you when the event details are announced.");

        $this->assertDatabaseHas('event_reminders', [
            'user_id' => $this->customerUser->id,
            'event_id' => $event->id,
            'status' => EventReminder::STATUS_ACTIVE,
            'notification_status' => EventReminder::STATUS_PENDING,
        ]);
    }

    /** 6. Duplicate reminder subscriptions are prevented */
    public function test_duplicate_reminder_subscriptions_are_prevented(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Cyber Summit 2030',
            'slug' => 'cyber-summit-2030',
            'description' => 'Upcoming tech summit.',
            'status' => 'published',
            'event_date' => null,
            'start_time' => null,
            'is_date_confirmed' => false,
        ]);

        // First subscription
        $this->actingAs($this->customerUser)->post(route('events.reminders.store', $event->slug));
        $this->assertEquals(1, EventReminder::where('user_id', $this->customerUser->id)->where('event_id', $event->id)->count());

        // Attempting duplicate subscription
        $secondResponse = $this->actingAs($this->customerUser)->post(route('events.reminders.store', $event->slug));
        $secondResponse->assertSessionHas('status', "You're subscribed! We'll email you when the event details are announced.");

        // Count must still be exactly 1
        $this->assertEquals(1, EventReminder::where('user_id', $this->customerUser->id)->where('event_id', $event->id)->count());
    }

    /** 7. Customer disables / cancels reminder */
    public function test_customer_can_cancel_reminder_subscription(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Cosmic Beats Festival',
            'slug' => 'cosmic-beats-festival',
            'description' => 'Outdoor music experience.',
            'status' => 'published',
            'event_date' => null,
            'start_time' => null,
            'is_date_confirmed' => false,
        ]);

        // Subscribe
        $this->actingAs($this->customerUser)->post(route('events.reminders.store', $event->slug));
        $this->assertEquals(1, EventReminder::where('user_id', $this->customerUser->id)->where('event_id', $event->id)->count());

        // Cancel
        $deleteResponse = $this->actingAs($this->customerUser)->delete(route('events.reminders.destroy', $event->slug));
        $deleteResponse->assertSessionHas('status', __('Reminder cancelled.'));

        $this->assertEquals(0, EventReminder::where('user_id', $this->customerUser->id)->where('event_id', $event->id)->count());
    }

    /** 8 & 9. Vendor adds valid dates and required details -> Subscribed users receive announcement email */
    public function test_vendor_confirming_dates_triggers_announcement_email_to_subscribers(): void
    {
        Mail::fake();

        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Interstellar Art Fair',
            'slug' => 'interstellar-art-fair',
            'description' => 'A premier modern art gathering.',
            'status' => 'published',
            'event_date' => null,
            'start_time' => null,
            'is_date_confirmed' => false,
            'event_type' => null,
            'base_price' => null,
        ]);

        // Customer 1 subscribes
        EventReminder::create([
            'user_id' => $this->customerUser->id,
            'event_id' => $event->id,
            'channel' => EventReminder::CHANNEL_EMAIL,
            'status' => EventReminder::STATUS_ACTIVE,
            'notification_status' => EventReminder::STATUS_PENDING,
            'created_at' => now()->subHour(),
        ]);

        // Customer 2 subscribes
        EventReminder::create([
            'user_id' => $this->customerUser2->id,
            'event_id' => $event->id,
            'channel' => EventReminder::CHANNEL_EMAIL,
            'status' => EventReminder::STATUS_ACTIVE,
            'notification_status' => EventReminder::STATUS_PENDING,
            'created_at' => now()->subMinutes(30),
        ]);

        // Vendor confirms dates & details
        $confirmedDate = Carbon::now()->addDays(20)->format('Y-m-d');
        $response = $this->actingAs($this->vendorUser)->put(route('vendor.events.update', $event), [
            'title' => 'Interstellar Art Fair',
            'category_id' => $this->category->id,
            'status' => 'published',
            'event_type' => 'paid',
            'base_price' => '45.00',
            'event_date' => $confirmedDate,
            'start_time' => '18:00',
            'end_time' => '22:00',
            'is_date_confirmed' => '1',
            'description' => 'A premier modern art gathering with confirmed dates.',
            'city' => 'San Francisco',
            'venue_name' => 'Pier 27 Cruise Terminal',
            'audience' => ['all_ages'],
        ]);

        $response->assertRedirect(route('vendor.events.edit', $event));

        $event->refresh();
        $this->assertTrue((bool) $event->is_date_confirmed);
        $this->assertNotNull($event->dates_announced_at);

        // Verify announcement emails were dispatched to both subscribers
        Mail::assertSent(EventDatesAnnouncedMail::class, 2);
        Mail::assertSent(EventDatesAnnouncedMail::class, fn ($m) => $m->hasTo('alice@example.com'));
        Mail::assertSent(EventDatesAnnouncedMail::class, fn ($m) => $m->hasTo('bob@example.com'));

        // Verify reminder records updated with notified status and timestamp
        $reminders = EventReminder::where('event_id', $event->id)->get();
        foreach ($reminders as $reminder) {
            $this->assertEquals(EventReminder::STATUS_NOTIFIED, $reminder->notification_status);
            $this->assertNotNull($reminder->notified_at);
        }
    }

    /** 10. Users who did not subscribe receive no announcement email */
    public function test_non_subscribers_do_not_receive_announcement_email(): void
    {
        Mail::fake();

        $nonSubscriber = User::factory()->create([
            'email' => 'charlie_non_subscriber@example.com',
            'role' => 'user',
        ]);

        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Exclusive Jazz Lounge',
            'slug' => 'exclusive-jazz-lounge',
            'description' => 'Intimate live jazz performance.',
            'status' => 'published',
            'event_date' => null,
            'start_time' => null,
            'is_date_confirmed' => false,
        ]);

        // Only alice subscribes
        EventReminder::create([
            'user_id' => $this->customerUser->id,
            'event_id' => $event->id,
            'channel' => EventReminder::CHANNEL_EMAIL,
            'status' => EventReminder::STATUS_ACTIVE,
            'notification_status' => EventReminder::STATUS_PENDING,
            'created_at' => now()->subMinutes(10),
        ]);

        // Vendor confirms dates
        $confirmedDate = Carbon::now()->addDays(15)->format('Y-m-d');
        $this->actingAs($this->vendorUser)->put(route('vendor.events.update', $event), [
            'title' => 'Exclusive Jazz Lounge',
            'category_id' => $this->category->id,
            'status' => 'published',
            'event_type' => 'paid',
            'base_price' => '30.00',
            'event_date' => $confirmedDate,
            'start_time' => '20:00',
            'is_date_confirmed' => '1',
            'description' => 'Intimate live jazz performance with confirmed dates.',
            'audience' => ['adults'],
        ]);

        // Mail sent to alice, but NOT to charlie
        Mail::assertSent(EventDatesAnnouncedMail::class, fn ($m) => $m->hasTo('alice@example.com'));
        Mail::assertNotSent(EventDatesAnnouncedMail::class, fn ($m) => $m->hasTo('charlie_non_subscriber@example.com'));
    }

    /** 11. Repeated saves do not generate duplicate emails */
    public function test_repeated_saves_do_not_generate_duplicate_emails(): void
    {
        Mail::fake();

        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Global Hackathon 2027',
            'slug' => 'global-hackathon-2027',
            'description' => '48 hour coding marathon.',
            'status' => 'published',
            'event_date' => null,
            'start_time' => null,
            'is_date_confirmed' => false,
        ]);

        EventReminder::create([
            'user_id' => $this->customerUser->id,
            'event_id' => $event->id,
            'channel' => EventReminder::CHANNEL_EMAIL,
            'status' => EventReminder::STATUS_ACTIVE,
            'notification_status' => EventReminder::STATUS_PENDING,
            'created_at' => now()->subDay(),
        ]);

        // First save with confirmed dates: triggers emails
        $confirmedDate = Carbon::now()->addDays(30)->format('Y-m-d');
        $this->actingAs($this->vendorUser)->put(route('vendor.events.update', $event), [
            'title' => 'Global Hackathon 2027',
            'category_id' => $this->category->id,
            'status' => 'published',
            'event_type' => 'free',
            'base_price' => '0',
            'event_date' => $confirmedDate,
            'start_time' => '09:00',
            'is_date_confirmed' => '1',
            'description' => 'Updated description.',
            'audience' => ['all_ages'],
        ]);

        Mail::assertSent(EventDatesAnnouncedMail::class, 1);

        // Second save (updating description or unrelated field): must NOT re-trigger announcement emails
        $this->actingAs($this->vendorUser)->put(route('vendor.events.update', $event), [
            'title' => 'Global Hackathon 2027 - Updated Schedule',
            'category_id' => $this->category->id,
            'status' => 'published',
            'event_type' => 'free',
            'base_price' => '0',
            'event_date' => $confirmedDate,
            'start_time' => '09:00',
            'is_date_confirmed' => '1',
            'description' => 'Second save with tiny description tweak.',
            'audience' => ['all_ages'],
        ]);

        // Still exactly 1 email sent in total
        Mail::assertSent(EventDatesAnnouncedMail::class, 1);
    }

    /** 12. Incomplete or invalid updates do not trigger notifications */
    public function test_incomplete_updates_without_confirmed_dates_do_not_trigger_notifications(): void
    {
        Mail::fake();

        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Indie Film Screening',
            'slug' => 'indie-film-screening',
            'description' => 'Underground indie films.',
            'status' => 'published',
            'event_date' => null,
            'start_time' => null,
            'is_date_confirmed' => false,
        ]);

        EventReminder::create([
            'user_id' => $this->customerUser->id,
            'event_id' => $event->id,
            'channel' => EventReminder::CHANNEL_EMAIL,
            'status' => EventReminder::STATUS_ACTIVE,
            'notification_status' => EventReminder::STATUS_PENDING,
            'created_at' => now()->subDay(),
        ]);

        // Vendor updates description but keeps is_date_confirmed unchecked
        $this->actingAs($this->vendorUser)->put(route('vendor.events.update', $event), [
            'title' => 'Indie Film Screening',
            'category_id' => $this->category->id,
            'status' => 'published',
            'event_type' => '',
            'base_price' => '',
            'event_date' => '',
            'start_time' => '',
            'is_date_confirmed' => '0',
            'description' => 'Updated teaser without dates.',
            'audience' => ['adults'],
        ]);

        Mail::assertNothingSent();
        $this->assertNull($event->fresh()->dates_announced_at);
    }

    public function test_announcement_only_sends_to_active_email_subscriptions(): void
    {
        Mail::fake();

        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Email Channel Check',
            'slug' => 'email-channel-check',
            'status' => 'published',
            'event_date' => Carbon::now()->addDays(8)->format('Y-m-d'),
            'start_time' => '18:00',
            'is_date_confirmed' => true,
            'event_type' => 'free',
        ]);

        $emailReminder = EventReminder::create([
            'user_id' => $this->customerUser->id,
            'event_id' => $event->id,
            'channel' => EventReminder::CHANNEL_EMAIL,
            'status' => EventReminder::STATUS_ACTIVE,
            'notification_status' => EventReminder::STATUS_PENDING,
        ]);
        $calendarReminder = EventReminder::create([
            'user_id' => $this->customerUser2->id,
            'event_id' => $event->id,
            'channel' => 'calendar',
            'status' => EventReminder::STATUS_ACTIVE,
            'notification_status' => EventReminder::STATUS_PENDING,
        ]);

        app(EventNotificationService::class)->notifySubscribersOfAnnouncedDates($event);

        Mail::assertSent(EventDatesAnnouncedMail::class, 1);
        $this->assertSame(EventReminder::STATUS_SENT, $emailReminder->fresh()->notification_status);
        $this->assertSame(EventReminder::STATUS_PENDING, $calendarReminder->fresh()->notification_status);
    }

    public function test_one_recipient_failure_does_not_stop_other_reminder_emails(): void
    {
        Queue::fake();
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Recipient Failure Isolation',
            'slug' => 'recipient-failure-isolation',
            'status' => 'published',
            'event_date' => Carbon::now()->addDays(9)->format('Y-m-d'),
            'start_time' => '19:00',
            'is_date_confirmed' => true,
            'event_type' => 'free',
        ]);

        $failedReminder = EventReminder::create([
            'user_id' => $this->customerUser->id,
            'event_id' => $event->id,
            'channel' => EventReminder::CHANNEL_EMAIL,
            'status' => EventReminder::STATUS_ACTIVE,
            'notification_status' => EventReminder::STATUS_PENDING,
        ]);
        $successfulReminder = EventReminder::create([
            'user_id' => $this->customerUser2->id,
            'event_id' => $event->id,
            'channel' => EventReminder::CHANNEL_EMAIL,
            'status' => EventReminder::STATUS_ACTIVE,
            'notification_status' => EventReminder::STATUS_PENDING,
        ]);

        $pendingMail = \Mockery::mock(\Illuminate\Mail\PendingMail::class);
        $pendingMail->shouldReceive('send')->once()->with(\Mockery::type(EventDatesAnnouncedMail::class));
        Mail::shouldReceive('to')->once()->with('alice@example.com')
            ->andThrow(new \RuntimeException('SMTP failed, password=should-not-be-stored'));
        Mail::shouldReceive('to')->once()->with('bob@example.com')->andReturn($pendingMail);

        $queuedCount = app(EventNotificationService::class)->notifySubscribersOfAnnouncedDates($event);
        $this->assertSame(2, $queuedCount);
        Queue::assertPushed(SendEventReminderEmail::class, 2);

        try {
            (new SendEventReminderEmail($failedReminder->id, 'test-correlation'))->handle();
            $this->fail('Expected the failed recipient transport to throw.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('SMTP failed', $exception->getMessage());
            (new SendEventReminderEmail($failedReminder->id, 'test-correlation'))->failed($exception);
        }
        (new SendEventReminderEmail($successfulReminder->id, 'test-correlation'))->handle();

        $this->assertSame(EventReminder::STATUS_FAILED, $failedReminder->fresh()->notification_status);
        $this->assertStringNotContainsString('should-not-be-stored', $failedReminder->fresh()->notification_error);
        $this->assertSame(EventReminder::STATUS_SENT, $successfulReminder->fresh()->notification_status);
    }

    public function test_announcement_email_renders_event_details_and_only_shows_booking_when_available(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Rendered Reminder Event',
            'slug' => 'rendered-reminder-event',
            'description' => 'A rendered event description.',
            'venue_name' => 'Pier 27',
            'city' => 'San Francisco',
            'status' => 'published',
            'event_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
            'start_time' => '18:00',
            'is_date_confirmed' => true,
            'event_type' => 'free',
        ]);

        $html = (new EventDatesAnnouncedMail($event, $this->customerUser))->render();

        $this->assertStringContainsString('Rendered Reminder Event', $html);
        $this->assertStringContainsString('Pier 27, San Francisco', $html);
        $this->assertStringContainsString(route('events.show', $event->slug) . '#tickets', $html);
        $this->assertStringContainsString('Book Your Tickets Now', $html);

        $event->update(['event_type' => null]);
        $htmlWithoutBooking = (new EventDatesAnnouncedMail($event->fresh(), $this->customerUser))->render();
        $this->assertStringNotContainsString(route('events.show', $event->slug) . '#tickets', $htmlWithoutBooking);
    }

    /** 13. Failed email delivery can be safely retried */
    public function test_failed_notification_is_tracked_and_can_be_retried(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Symphony Night',
            'slug' => 'symphony-night',
            'description' => 'Orchestral night.',
            'status' => 'published',
            'event_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'start_time' => '19:00',
            'is_date_confirmed' => true,
            'dates_announced_at' => now(),
            'event_type' => 'free',
        ]);

        // Create a failed reminder record
        $failedReminder = EventReminder::create([
            'user_id' => $this->customerUser->id,
            'event_id' => $event->id,
            'channel' => EventReminder::CHANNEL_EMAIL,
            'status' => EventReminder::STATUS_ACTIVE,
            'notification_status' => EventReminder::STATUS_FAILED,
            'notification_error' => 'Connection timeout to SMTP server',
            'retry_count' => 1,
            'created_at' => now()->subHours(2),
        ]);

        $this->assertTrue($failedReminder->isFailed());

        Mail::fake();

        // Vendor triggers retry endpoint
        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.reminders.retry', $event));
        $response->assertSessionHas('status');

        // Check that retry attempted delivery
        Mail::assertSent(EventDatesAnnouncedMail::class, 1);

        $failedReminder->refresh();
        $this->assertEquals(EventReminder::STATUS_NOTIFIED, $failedReminder->notification_status);
        $this->assertNotNull($failedReminder->notified_at);
        $this->assertEquals(2, $failedReminder->retry_count);
    }

    /** 14. Booking opens only when all prerequisites are satisfied */
    public function test_booking_opens_only_when_all_prerequisites_are_satisfied(): void
    {
        // Case A: Date confirmed, but event_type is missing/TBA -> booking not eligible
        $eventA = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Event A - Missing Type',
            'slug' => 'event-a-missing-type',
            'status' => 'published',
            'event_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'start_time' => '10:00',
            'is_date_confirmed' => true,
            'event_type' => null,
            'base_price' => null,
        ]);
        $this->assertFalse($eventA->isBookingEligible());

        // Case B: Paid event, but base_price is 0 and no ticket types -> booking not eligible
        $eventB = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Event B - Paid Without Price',
            'slug' => 'event-b-paid-without-price',
            'status' => 'published',
            'event_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'start_time' => '10:00',
            'is_date_confirmed' => true,
            'event_type' => 'paid',
            'base_price' => 0,
        ]);
        $this->assertFalse($eventB->isBookingEligible());

        // Case C: Paid event with positive base price and confirmed date -> booking eligible!
        $eventC = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Event C - Ready For Booking',
            'slug' => 'event-c-ready-for-booking',
            'status' => 'published',
            'event_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'start_time' => '10:00',
            'is_date_confirmed' => true,
            'event_type' => 'paid',
            'base_price' => 50.00,
        ]);
        $this->assertTrue($eventC->isBookingEligible());
        $this->assertTrue($eventC->isCalendarEligible());

        // Case D: Free event with confirmed date -> booking eligible!
        $eventD = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Event D - Free Admission Ready',
            'slug' => 'event-d-free-admission-ready',
            'status' => 'published',
            'event_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'start_time' => '10:00',
            'is_date_confirmed' => true,
            'event_type' => 'free',
            'base_price' => 0,
        ]);
        $this->assertTrue($eventD->isBookingEligible());

        // Can successfully complete free registration
        $freeBookingResponse = $this->actingAs($this->customerUser)->post(route('checkout.session', $eventD->slug), [
            'quantity' => 1,
        ]);
        $freeBookingResponse->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customerUser->id,
            'event_id' => $eventD->id,
            'status' => 'paid',
        ]);
    }

    /** 15. Existing event publishing and booking functionality continues to work */
    public function test_existing_standard_event_workflow_continues_to_work(): void
    {
        // Standard event created with confirmed date and time from the start
        $standardDate = Carbon::now()->addDays(14)->format('Y-m-d');
        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), [
            'title' => 'Traditional Opera Gala',
            'category_id' => $this->category->id,
            'status' => 'published',
            'event_type' => 'free',
            'base_price' => '0',
            'event_date' => $standardDate,
            'start_time' => '19:30',
            'end_time' => '22:30',
            'is_date_confirmed' => '1',
            'description' => 'A traditional opera evening.',
            'city' => 'San Francisco',
            'audience' => ['all_ages'],
        ]);

        $response->assertRedirect();

        $event = Event::where('title', 'Traditional Opera Gala')->first();
        $this->assertNotNull($event);
        $this->assertEquals('published', $event->status);
        $this->assertTrue($event->isDateConfirmed());
        $this->assertTrue($event->isBookingEligible());
        $this->assertTrue($event->isCalendarEligible());

        // .ics calendar works
        $icsResponse = $this->get(route('events.calendar.ics', $event->slug));
        $icsResponse->assertStatus(200);
        $this->assertStringContainsString('BEGIN:VCALENDAR', $icsResponse->getContent());

        // Free ticket registration works
        $checkoutResponse = $this->actingAs($this->customerUser)->post(route('checkout.session', $event->slug), [
            'quantity' => 2,
        ]);
        $checkoutResponse->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customerUser->id,
            'event_id' => $event->id,
            'status' => 'paid',
        ]);
    }
}
