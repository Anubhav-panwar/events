<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->artisan('migrate');
        $this->seed();
    }

    public function test_vendor_can_access_dashboard(): void
    {
        $vendor = User::firstWhere('email', 'vendor@example.com');
        $this->actingAs($vendor);
        $this->get('/vendor/dashboard')->assertStatus(200);
    }

    public function test_user_cannot_access_vendor_dashboard(): void
    {
        $user = User::firstWhere('email', 'user@example.com');
        $this->actingAs($user);
        $this->get('/vendor/dashboard')->assertStatus(403);
    }
}
