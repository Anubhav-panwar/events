<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventMedia;
use App\Models\EventShareClick;
use App\Models\TicketType;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorShareClick;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarAndSharingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $vendorUser;
    private VendorProfile $vendorProfile;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendorUser = User::factory()->create([
            'role' => 'vendor',
            'email_verified_at' => now(),
        ]);

        $this->vendorProfile = VendorProfile::create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'Apex Entertainment Lab',
            'slug' => 'apex-entertainment-lab',
            'description' => 'Premier live event organizer producing world-class concerts and experiences.',
            'city' => 'Berlin',
            'country' => 'Germany',
            'address' => 'Friedrichstraße 100',
            'is_approved' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Concerts',
            'slug' => 'concerts',
        ]);
    }

    public function test_confirmed_event_is_calendar_eligible_and_generates_valid_calendar_links(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Symphony Under the Stars',
            'slug' => 'symphony-under-the-stars',
            'description' => 'A magical orchestral evening featuring classical masterpieces.',
            'venue_name' => 'Berlin Philharmonic Plaza',
            'address' => 'Herbert-von-Karajan-Str. 1',
            'city' => 'Berlin',
            'country' => 'Germany',
            'event_date' => '2026-08-15',
            'start_time' => '19:00',
            'end_time' => '22:00',
            'audience' => ['All Ages'],
            'status' => 'published',
            'is_date_confirmed' => true,
            'event_type' => 'free',
            'base_price' => 0,
        ]);

        $this->assertTrue($event->isCalendarEligible());

        $googleUrl = $event->googleCalendarUrl();
        $this->assertNotNull($googleUrl);
        $this->assertStringContainsString('calendar.google.com/calendar/render', $googleUrl);
        $this->assertStringContainsString('action=TEMPLATE', $googleUrl);
        $this->assertStringContainsString('Symphony+Under+the+Stars', $googleUrl);
        $this->assertStringContainsString('Herbert-von-Karajan-Str.+1', $googleUrl);

        $outlookUrl = $event->outlookCalendarUrl();
        $this->assertNotNull($outlookUrl);
        $this->assertStringContainsString('outlook.live.com/calendar/0/deeplink/compose', $outlookUrl);
        $this->assertStringContainsString('rru=addevent', $outlookUrl);

        $yahooUrl = $event->yahooCalendarUrl();
        $this->assertNotNull($yahooUrl);
        $this->assertStringContainsString('calendar.yahoo.com', $yahooUrl);

        // Native .ics download
        $response = $this->get(route('events.ics', $event->slug));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="symphony-under-the-stars.ics"');

        $icsContent = $response->getContent();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $icsContent);
        $this->assertStringContainsString('VERSION:2.0', $icsContent);
        $this->assertStringContainsString('PRODID:-//Huddle Events//EN', $icsContent);
        $this->assertStringContainsString('STATUS:CONFIRMED', $icsContent);
        $this->assertStringContainsString('SUMMARY:Symphony Under the Stars', $icsContent);
        $this->assertStringContainsString('LOCATION:Berlin Philharmonic Plaza', $icsContent);
        $this->assertStringContainsString('END:VCALENDAR', $icsContent);
    }

    public function test_unconfirmed_tentative_event_disables_calendar_integration_and_rejects_ics(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Indie Rock Summit Tentative',
            'slug' => 'indie-rock-summit-tentative',
            'description' => 'Date and lineup pending venue confirmation.',
            'event_date' => '2026-09-20',
            'start_time' => '20:00',
            'end_time' => '23:00',
            'audience' => ['Adults'],
            'status' => 'published',
            'is_date_confirmed' => false, // UNCONFIRMED / TENTATIVE
            'event_type' => 'free',
            'base_price' => 0,
        ]);

        $this->assertFalse($event->isCalendarEligible());
        $this->assertNull($event->googleCalendarUrl());
        $this->assertNull($event->outlookCalendarUrl());
        $this->assertNull($event->yahooCalendarUrl());

        // Attempting to download .ics for unconfirmed event must fail with HTTP 422
        $response = $this->get(route('events.ics', $event->slug));
        $response->assertStatus(422);

        // Event show page renders warning notice and disables calendar export
        $pageResponse = $this->get(route('events.show', $event->slug));
        $pageResponse->assertOk();
        $pageResponse->assertSee(__('Schedule Pending Confirmation'));
        $pageResponse->assertSee(__('Date Tentative'));
        $pageResponse->assertDontSee(__('Download .ICS (iPhone/Android/Outlook)'));
    }

    public function test_draft_event_is_not_calendar_eligible(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Unpublished Draft Gig',
            'slug' => 'unpublished-draft-gig',
            'description' => 'Draft event not yet public.',
            'event_date' => '2026-10-10',
            'start_time' => '18:00',
            'audience' => ['All Ages'],
            'status' => 'draft',
            'is_date_confirmed' => true,
            'event_type' => 'free',
        ]);

        $this->assertFalse($event->isCalendarEligible());
    }

    public function test_event_share_via_channels_records_clicks_and_redirects(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Jazz at the Park',
            'slug' => 'jazz-at-the-park',
            'description' => 'Smooth jazz evening.',
            'event_date' => '2026-07-25',
            'start_time' => '19:00',
            'audience' => ['All Ages'],
            'status' => 'published',
            'is_date_confirmed' => true,
            'event_type' => 'free',
        ]);

        $user = User::factory()->create();

        // 1. WhatsApp share
        $waResponse = $this->actingAs($user)->get(route('events.share', ['slug' => $event->slug, 'channel' => 'whatsapp']));
        $waResponse->assertRedirect();
        $this->assertStringContainsString('wa.me', $waResponse->headers->get('Location'));
        $this->assertDatabaseHas('event_share_clicks', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'channel' => 'whatsapp',
        ]);

        // 2. Telegram share
        $tgResponse = $this->actingAs($user)->get(route('events.share', ['slug' => $event->slug, 'channel' => 'telegram']));
        $tgResponse->assertRedirect();
        $this->assertStringContainsString('t.me/share/url', $tgResponse->headers->get('Location'));
        $this->assertDatabaseHas('event_share_clicks', [
            'event_id' => $event->id,
            'channel' => 'telegram',
        ]);

        // 3. Email share
        $emailResponse = $this->actingAs($user)->get(route('events.share', ['slug' => $event->slug, 'channel' => 'email']));
        $emailResponse->assertRedirect();
        $this->assertStringContainsString('mailto:', $emailResponse->headers->get('Location'));
        $this->assertDatabaseHas('event_share_clicks', [
            'event_id' => $event->id,
            'channel' => 'email',
        ]);

        // 4. Copy share (returns JSON with public URL)
        $copyResponse = $this->actingAs($user)->get(route('events.share', ['slug' => $event->slug, 'channel' => 'copy']));
        $copyResponse->assertOk();
        $copyResponse->assertJsonStructure(['url']);
        $this->assertStringContainsString('/e/jazz-at-the-park', $copyResponse->json('url'));
        $this->assertDatabaseHas('event_share_clicks', [
            'event_id' => $event->id,
            'channel' => 'copy',
        ]);
    }

    public function test_vendor_profile_share_via_channels_records_clicks_and_redirects(): void
    {
        $user = User::factory()->create();

        // 1. WhatsApp share
        $waResponse = $this->actingAs($user)->get(route('vendors.share', [
            'slug' => $this->vendorProfile->slug,
            'channel' => 'whatsapp',
        ]));
        $waResponse->assertRedirect();
        $this->assertStringContainsString('wa.me', $waResponse->headers->get('Location'));
        $this->assertDatabaseHas('vendor_share_clicks', [
            'vendor_profile_id' => $this->vendorProfile->id,
            'user_id' => $user->id,
            'channel' => 'whatsapp',
        ]);

        // 2. X (Twitter) share
        $xResponse = $this->actingAs($user)->get(route('vendors.share', [
            'slug' => $this->vendorProfile->slug,
            'channel' => 'x',
        ]));
        $xResponse->assertRedirect();
        $this->assertStringContainsString('twitter.com/intent/tweet', $xResponse->headers->get('Location'));
        $this->assertDatabaseHas('vendor_share_clicks', [
            'vendor_profile_id' => $this->vendorProfile->id,
            'channel' => 'x',
        ]);

        // 3. Telegram share
        $tgResponse = $this->actingAs($user)->get(route('vendors.share', [
            'slug' => $this->vendorProfile->slug,
            'channel' => 'telegram',
        ]));
        $tgResponse->assertRedirect();
        $this->assertStringContainsString('t.me/share/url', $tgResponse->headers->get('Location'));
        $this->assertDatabaseHas('vendor_share_clicks', [
            'vendor_profile_id' => $this->vendorProfile->id,
            'channel' => 'telegram',
        ]);

        // 4. Email share
        $emailResponse = $this->actingAs($user)->get(route('vendors.share', [
            'slug' => $this->vendorProfile->slug,
            'channel' => 'email',
        ]));
        $emailResponse->assertRedirect();
        $this->assertStringContainsString('mailto:', $emailResponse->headers->get('Location'));
        $this->assertDatabaseHas('vendor_share_clicks', [
            'vendor_profile_id' => $this->vendorProfile->id,
            'channel' => 'email',
        ]);

        // 5. Copy channel returns JSON with exact vendor profile URL
        $copyResponse = $this->actingAs($user)->get(route('vendors.share', [
            'slug' => $this->vendorProfile->slug,
            'channel' => 'copy',
        ]));
        $copyResponse->assertOk();
        $copyResponse->assertJson([
            'url' => route('vendors.show', $this->vendorProfile->slug),
        ]);
        $this->assertDatabaseHas('vendor_share_clicks', [
            'vendor_profile_id' => $this->vendorProfile->id,
            'channel' => 'copy',
        ]);
    }

    public function test_shared_links_open_the_correct_public_event_and_vendor_profile(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Neon Electronic Night',
            'slug' => 'neon-electronic-night',
            'description' => 'A dazzling electronic festival experience.',
            'event_date' => '2026-11-20',
            'start_time' => '21:00',
            'audience' => ['Adults'],
            'status' => 'published',
            'is_date_confirmed' => true,
            'event_type' => 'free',
        ]);

        // Event public page
        $eventPage = $this->get(route('events.show', $event->slug));
        $eventPage->assertOk();
        $eventPage->assertSee('Neon Electronic Night');
        $eventPage->assertSee('og:url', false);
        $eventPage->assertSee(route('events.show', $event->slug));

        // Vendor public page
        $vendorPage = $this->get(route('vendors.show', $this->vendorProfile->slug));
        $vendorPage->assertOk();
        $vendorPage->assertSee('Apex Entertainment Lab');
        $vendorPage->assertSee('og:url', false);
        $vendorPage->assertSee(route('vendors.show', $this->vendorProfile->slug));
        $vendorPage->assertSee(__('Share Profile'));
    }

    public function test_vendor_can_toggle_is_date_confirmed_on_event_create_and_edit(): void
    {
        // 1. Create with tentative date (is_date_confirmed = 0)
        $createResponse = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), [
            'title' => 'Acoustic Weekend Tentative',
            'category_id' => $this->category->id,
            'event_date' => '2026-09-12',
            'start_time' => '17:00',
            'audience' => ['All Ages'],
            'status' => 'published',
            'event_type' => 'free',
            'is_date_confirmed' => '0',
        ]);

        $createResponse->assertRedirect();
        $event = Event::where('title', 'Acoustic Weekend Tentative')->firstOrFail();
        $this->assertFalse($event->is_date_confirmed);
        $this->assertFalse($event->isCalendarEligible());

        // 2. Edit event to confirm schedule (is_date_confirmed = 1)
        $editResponse = $this->actingAs($this->vendorUser)->put(route('vendor.events.update', $event), [
            'title' => 'Acoustic Weekend Confirmed',
            'category_id' => $this->category->id,
            'event_date' => '2026-09-12',
            'start_time' => '17:00',
            'audience' => ['All Ages'],
            'status' => 'published',
            'event_type' => 'free',
            'is_date_confirmed' => '1',
        ]);

        $editResponse->assertRedirect();
        $event->refresh();
        $this->assertTrue($event->is_date_confirmed);
        $this->assertTrue($event->isCalendarEligible());
    }

    public function test_multilingual_calendar_and_sharing_labels(): void
    {
        $event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Multilingual Global Gala',
            'slug' => 'multilingual-global-gala',
            'description' => 'International celebration.',
            'event_date' => '2026-12-05',
            'start_time' => '18:00',
            'audience' => ['All Ages'],
            'status' => 'published',
            'is_date_confirmed' => true,
            'event_type' => 'free',
        ]);

        // English
        $enResponse = $this->get('/e/' . $event->slug . '?lang=en');
        $enResponse->assertOk();
        $enResponse->assertSee(__('Save to Calendar'));
        $enResponse->assertSee(__('Share with Friends'));

        // German
        $deResponse = $this->get('/e/' . $event->slug . '?lang=de');
        $deResponse->assertOk();
        $deResponse->assertSee('Im Kalender speichern');
        $deResponse->assertSee('Mit Freunden teilen');

        // Italian
        $itResponse = $this->get('/e/' . $event->slug . '?lang=it');
        $itResponse->assertOk();
        $itResponse->assertSee('Salva nel calendario');
        $itResponse->assertSee('Condividi con gli amici');
    }
}
