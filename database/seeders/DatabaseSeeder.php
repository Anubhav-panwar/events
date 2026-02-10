<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\VendorProfile;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        collect([
            ['name' => 'Music', 'slug' => 'music'],
            ['name' => 'Sports', 'slug' => 'sports'],
            ['name' => 'Workshop', 'slug' => 'workshop'],
            ['name' => 'Food & Drink', 'slug' => 'food-drink'],
        ])->each(fn($c) => Category::firstOrCreate(['slug' => $c['slug']], $c));
        $catMusic = Category::where('slug', 'music')->first();

        $admin = User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $vendor = User::updateOrCreate(['email' => 'vendor@example.com'], [
            'name' => 'Acme Vendor',
            'password' => Hash::make('password'),
            'role' => 'vendor',
        ]);

        $user = User::updateOrCreate(['email' => 'user@example.com'], [
            'name' => 'Regular User',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $profile = VendorProfile::updateOrCreate(['user_id' => $vendor->id], [
            'business_name' => 'Acme Events',
            'slug' => 'acme-events',
            'description' => 'We host great gatherings and concerts.',
            'phone' => '+1-555-1234',
            'website' => 'https://example.com',
            'instagram' => 'https://instagram.com/example',
            'facebook' => 'https://facebook.com/example',
            'twitter' => 'https://twitter.com/example',
            'address' => '123 Main St, City',
            'city' => 'San Francisco',
            'country' => 'USA',
            'latitude' => 37.7749,
            'longitude' => -122.4194,
            'is_approved' => true,
        ]);

        if ($catMusic) {
            $profile->categories()->sync([$catMusic->id]);
        }

        $event = Event::updateOrCreate(['slug' => 'opening-night-demo'], [
            'vendor_profile_id' => $profile->id,
            'title' => 'Opening Night Demo',
            'description' => 'Join us for an unforgettable evening.',
            'venue_name' => 'City Auditorium',
            'event_date' => now()->addWeek()->toDateString(),
            'start_time' => '18:00',
            'end_time' => '21:00',
            'address' => '123 Main St, City',
            'city' => 'San Francisco',
            'country' => 'USA',
            'latitude' => 37.7749,
            'longitude' => -122.4194,
            'category_id' => $catMusic?->id,
            'capacity' => 100,
            'status' => 'published',
            'is_featured' => true,
            'event_type' => 'paid',
            'base_price' => 25,
        ]);

        TicketType::updateOrCreate(['event_id' => $event->id, 'name' => 'General Admission'], [
            'price' => 25,
            'currency' => 'USD',
            'quantity' => 100,
        ]);
    }
}
