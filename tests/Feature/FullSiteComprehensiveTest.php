<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullSiteComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_can_access_all_public_pages(): void
    {
        $publicUrls = [
            '/',
            '/about',
            '/services',
            '/pricing',
            '/portfolio',
            '/testimonials',
            '/faq',
            '/contact',
            '/events',
            '/search?q=opening',
            '/e/opening-night-demo',
            '/e/opening-night-demo/ics',
            '/vendors',
            '/vendors/acme-events',
            '/login',
            '/register',
            '/forgot-password',
        ];

        foreach ($publicUrls as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }

        $shareResponse = $this->get('/e/opening-night-demo/share/whatsapp');
        $shareResponse->assertStatus(302);
    }

    public function test_authenticated_user_can_interact_with_event_and_vendor(): void
    {
        $user = User::where('role', 'user')->first();
        $this->actingAs($user);

        // 1. Viewing event as authenticated user (the exact view where event_reminders is called)
        $eventShow = $this->get('/e/opening-night-demo');
        $eventShow->assertStatus(200);
        $eventShow->assertSee('Opening Night Demo');

        // 2. Setting reminder
        $reminderRes = $this->post('/events/opening-night-demo/reminders', [
            'minutes_before' => 1440,
            'channel' => 'calendar',
        ]);
        $reminderRes->assertRedirect();
        $this->assertDatabaseHas('event_reminders', [
            'user_id' => $user->id,
        ]);

        // 3. Deleting reminder
        $deleteReminderRes = $this->delete('/events/opening-night-demo/reminders');
        $deleteReminderRes->assertRedirect();
        $this->assertDatabaseMissing('event_reminders', [
            'user_id' => $user->id,
        ]);

        // 4. Saving event
        $saveRes = $this->post('/events/opening-night-demo/save');
        $saveRes->assertRedirect();
        $this->assertDatabaseHas('event_saves', [
            'user_id' => $user->id,
        ]);

        // 5. Unsaving event
        $unsaveRes = $this->delete('/events/opening-night-demo/save');
        $unsaveRes->assertRedirect();
        $this->assertDatabaseMissing('event_saves', [
            'user_id' => $user->id,
        ]);

        // 6. Following vendor
        $followRes = $this->post('/vendors/acme-events/follow');
        $followRes->assertRedirect();
        $this->assertDatabaseHas('vendor_follows', [
            'user_id' => $user->id,
        ]);

        // 7. Unfollowing vendor
        $unfollowRes = $this->delete('/vendors/acme-events/follow');
        $unfollowRes->assertRedirect();
        $this->assertDatabaseMissing('vendor_follows', [
            'user_id' => $user->id,
        ]);

        // 8. Account pages
        $this->get('/account/saved')->assertStatus(200);
        $this->get('/account/tickets')->assertStatus(200);
        $this->get('/profile')->assertStatus(200);
    }

    public function test_vendor_can_access_vendor_portal(): void
    {
        $vendor = User::where('role', 'vendor')->first();
        $this->actingAs($vendor);

        $this->get('/vendor/dashboard')->assertStatus(200);
        $this->get('/vendor/profile')->assertStatus(200);
        $this->get('/vendor/events/create')->assertStatus(200);
        $this->get('/vendor/orders')->assertStatus(200);
        $this->get('/vendor/checkin')->assertStatus(200);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $this->get('/admin')->assertStatus(200);
    }
}
