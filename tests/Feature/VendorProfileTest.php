<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->artisan('migrate');
        $this->seed();
    }

    public function test_vendor_can_update_profile(): void
    {
        $vendor = User::firstWhere('email', 'vendor@example.com');
        $this->actingAs($vendor);

        $res = $this->put('/vendor/profile', [
            'business_name' => 'Updated Vendor',
            'website' => 'https://example.com',
        ]);
        $res->assertRedirect('/vendor/profile');
    }
}
