<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventReview;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorReview;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventAndVendorReviewsTest extends TestCase
{
    use RefreshDatabase;

    private User $hostVendorUser;
    private VendorProfile $vendorProfile;
    private Category $category;
    private Event $event;
    private User $verifiedCustomerUser;
    private User $unverifiedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Music & Festivals',
            'slug' => 'music-festivals',
        ]);

        $this->hostVendorUser = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Host Vendor',
        ]);

        $this->vendorProfile = VendorProfile::create([
            'user_id' => $this->hostVendorUser->id,
            'business_name' => 'Starlight Productions',
            'slug' => 'starlight-productions',
            'city' => 'Berlin',
            'country' => 'Germany',
        ]);

        $this->event = Event::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Summer Neon Festival 2026',
            'slug' => 'summer-neon-festival-2026',
            'description' => 'A wonderful electronic summer open air.',
            'event_date' => Carbon::now()->addDays(5)->toDateString(),
            'start_time' => '18:00',
            'end_time' => '23:00',
            'base_price' => 29.99,
            'status' => 'published',
            'audience' => ['Adults', 'Teens'],
        ]);

        $this->verifiedCustomerUser = User::factory()->create([
            'role' => 'user',
            'name' => 'Alice Attendee',
        ]);

        $this->unverifiedUser = User::factory()->create([
            'role' => 'user',
            'name' => 'Bob Stranger',
        ]);

        // Create confirmed order for Alice
        Order::create([
            'user_id' => $this->verifiedCustomerUser->id,
            'vendor_profile_id' => $this->vendorProfile->id,
            'event_id' => $this->event->id,
            'total' => 29.99,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function test_guest_cannot_submit_event_or_vendor_review()
    {
        $response = $this->post(route('events.reviews.store', $this->event->slug), [
            'rating' => 5,
            'review' => 'Incredible concert!',
        ]);
        $response->assertRedirect(route('login'));

        $vendorResponse = $this->post(route('vendors.reviews.store', $this->vendorProfile->slug), [
            'rating' => 5,
            'review' => 'Great organizer!',
        ]);
        $vendorResponse->assertRedirect(route('login'));
    }

    public function test_unverified_attendee_cannot_review_event()
    {
        $response = $this->actingAs($this->unverifiedUser)
            ->from(route('events.show', $this->event->slug))
            ->post(route('events.reviews.store', $this->event->slug), [
                'rating' => 5,
                'review' => 'I did not actually book this.',
            ]);

        $response->assertRedirect(route('events.show', $this->event->slug));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('event_reviews', [
            'event_id' => $this->event->id,
            'user_id' => $this->unverifiedUser->id,
        ]);
    }

    public function test_host_vendor_cannot_review_own_event()
    {
        $response = $this->actingAs($this->hostVendorUser)
            ->from(route('events.show', $this->event->slug))
            ->post(route('events.reviews.store', $this->event->slug), [
                'rating' => 5,
                'review' => 'My event is the best!',
            ]);

        $response->assertRedirect(route('events.show', $this->event->slug));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('event_reviews', [
            'event_id' => $this->event->id,
            'user_id' => $this->hostVendorUser->id,
        ]);
    }

    public function test_verified_attendee_can_submit_and_update_event_review()
    {
        // 1. Submit initial review
        $response = $this->actingAs($this->verifiedCustomerUser)
            ->from(route('events.show', $this->event->slug))
            ->post(route('events.reviews.store', $this->event->slug), [
                'rating' => 5,
                'review' => 'Pure magic! Sound quality and lights were fantastic.',
            ]);

        $response->assertRedirect(route('events.show', $this->event->slug));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('event_reviews', [
            'event_id' => $this->event->id,
            'user_id' => $this->verifiedCustomerUser->id,
            'rating' => 5,
            'review' => 'Pure magic! Sound quality and lights were fantastic.',
            'is_verified_attendee' => true,
        ]);

        $this->assertEquals(1, EventReview::where('event_id', $this->event->id)->count());

        // 2. Update review (should updateOrCreate without duplicate)
        $updateResponse = $this->actingAs($this->verifiedCustomerUser)
            ->from(route('events.show', $this->event->slug))
            ->post(route('events.reviews.store', $this->event->slug), [
                'rating' => 4,
                'review' => 'Updated review: Still great but ended slightly early.',
            ]);

        $updateResponse->assertRedirect(route('events.show', $this->event->slug));

        $this->assertEquals(1, EventReview::where('event_id', $this->event->id)->count());
        $this->assertDatabaseHas('event_reviews', [
            'event_id' => $this->event->id,
            'user_id' => $this->verifiedCustomerUser->id,
            'rating' => 4,
            'review' => 'Updated review: Still great but ended slightly early.',
        ]);
    }

    public function test_event_page_displays_ratings_and_breakdown()
    {
        // Create 2 reviews
        EventReview::create([
            'event_id' => $this->event->id,
            'user_id' => $this->verifiedCustomerUser->id,
            'rating' => 5,
            'review' => 'Best experience of the summer!',
            'is_verified_attendee' => true,
        ]);

        $secondCustomer = User::factory()->create(['name' => 'Charlie Crowd']);
        Order::create([
            'user_id' => $secondCustomer->id,
            'vendor_profile_id' => $this->vendorProfile->id,
            'event_id' => $this->event->id,
            'status' => 'paid',
            'total' => 29.99,
        ]);
        EventReview::create([
            'event_id' => $this->event->id,
            'user_id' => $secondCustomer->id,
            'rating' => 4,
            'review' => 'Very well organized venue.',
            'is_verified_attendee' => true,
        ]);

        $response = $this->get(route('events.show', $this->event->slug));
        $response->assertStatus(200);

        // Average of 5 and 4 is 4.5
        $response->assertSee('4.5');
        $response->assertSee('2 reviews');
        $response->assertSee('Best experience of the summer!');
        $response->assertSee('Very well organized venue.');
        $response->assertSee('Alice Attendee');
        $response->assertSee('Charlie Crowd');
    }

    public function test_attendee_can_delete_own_event_review_and_others_forbidden()
    {
        $review = EventReview::create([
            'event_id' => $this->event->id,
            'user_id' => $this->verifiedCustomerUser->id,
            'rating' => 5,
            'review' => 'To be deleted.',
            'is_verified_attendee' => true,
        ]);

        // Stranger tries to delete -> 403
        $forbiddenResponse = $this->actingAs($this->unverifiedUser)
            ->delete(route('events.reviews.destroy', [$this->event->slug, $review->id]));
        $forbiddenResponse->assertStatus(403);

        $this->assertDatabaseHas('event_reviews', ['id' => $review->id]);

        // Owner deletes -> 302 back with status
        $deleteResponse = $this->actingAs($this->verifiedCustomerUser)
            ->from(route('events.show', $this->event->slug))
            ->delete(route('events.reviews.destroy', [$this->event->slug, $review->id]));

        $deleteResponse->assertRedirect(route('events.show', $this->event->slug));
        $this->assertDatabaseMissing('event_reviews', ['id' => $review->id]);
    }

    public function test_unverified_customer_cannot_review_vendor()
    {
        $response = $this->actingAs($this->unverifiedUser)
            ->from(route('vendors.show', $this->vendorProfile->slug))
            ->post(route('vendors.reviews.store', $this->vendorProfile->slug), [
                'rating' => 5,
                'review' => 'I never booked with them.',
            ]);

        $response->assertRedirect(route('vendors.show', $this->vendorProfile->slug));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('vendor_reviews', [
            'vendor_profile_id' => $this->vendorProfile->id,
            'user_id' => $this->unverifiedUser->id,
        ]);
    }

    public function test_vendor_cannot_review_own_profile()
    {
        $response = $this->actingAs($this->hostVendorUser)
            ->from(route('vendors.show', $this->vendorProfile->slug))
            ->post(route('vendors.reviews.store', $this->vendorProfile->slug), [
                'rating' => 5,
                'review' => 'We are 5 stars guaranteed.',
            ]);

        $response->assertRedirect(route('vendors.show', $this->vendorProfile->slug));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('vendor_reviews', [
            'vendor_profile_id' => $this->vendorProfile->id,
            'user_id' => $this->hostVendorUser->id,
        ]);
    }

    public function test_verified_customer_can_submit_and_update_vendor_review()
    {
        // 1. Submit vendor review
        $response = $this->actingAs($this->verifiedCustomerUser)
            ->from(route('vendors.show', $this->vendorProfile->slug))
            ->post(route('vendors.reviews.store', $this->vendorProfile->slug), [
                'rating' => 5,
                'review' => 'Excellent host, highly professional coordination!',
            ]);

        $response->assertRedirect(route('vendors.show', $this->vendorProfile->slug));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('vendor_reviews', [
            'vendor_profile_id' => $this->vendorProfile->id,
            'user_id' => $this->verifiedCustomerUser->id,
            'rating' => 5,
            'review' => 'Excellent host, highly professional coordination!',
            'is_verified_customer' => true,
        ]);

        $this->assertEquals(1, VendorReview::where('vendor_profile_id', $this->vendorProfile->id)->count());

        // 2. Update vendor review
        $updateResponse = $this->actingAs($this->verifiedCustomerUser)
            ->from(route('vendors.show', $this->vendorProfile->slug))
            ->post(route('vendors.reviews.store', $this->vendorProfile->slug), [
                'rating' => 4,
                'review' => 'Updated review: Still great overall.',
            ]);

        $updateResponse->assertRedirect(route('vendors.show', $this->vendorProfile->slug));
        $this->assertEquals(1, VendorReview::where('vendor_profile_id', $this->vendorProfile->id)->count());
        $this->assertDatabaseHas('vendor_reviews', [
            'vendor_profile_id' => $this->vendorProfile->id,
            'user_id' => $this->verifiedCustomerUser->id,
            'rating' => 4,
            'review' => 'Updated review: Still great overall.',
        ]);
    }

    public function test_vendor_profile_page_displays_average_rating_and_reviews()
    {
        VendorReview::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'user_id' => $this->verifiedCustomerUser->id,
            'rating' => 5,
            'review' => 'Reliable and responsive team.',
            'is_verified_customer' => true,
        ]);

        $response = $this->get(route('vendors.show', $this->vendorProfile->slug));
        $response->assertStatus(200);

        $response->assertSee('5.0 ★');
        $response->assertSeeText('Reviews & Ratings (1)');
        $response->assertSee('Reliable and responsive team.');
        $response->assertSee('Alice Attendee');
        $response->assertSee('Verified Customer');
    }

    public function test_customer_can_delete_own_vendor_review_and_others_forbidden()
    {
        $review = VendorReview::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'user_id' => $this->verifiedCustomerUser->id,
            'rating' => 5,
            'review' => 'Temporary feedback.',
            'is_verified_customer' => true,
        ]);

        // Unauthorized user cannot delete -> 403
        $forbiddenResponse = $this->actingAs($this->unverifiedUser)
            ->delete(route('vendors.reviews.destroy', [$this->vendorProfile->slug, $review->id]));
        $forbiddenResponse->assertStatus(403);

        $this->assertDatabaseHas('vendor_reviews', ['id' => $review->id]);

        // Customer deletes own review -> success
        $deleteResponse = $this->actingAs($this->verifiedCustomerUser)
            ->from(route('vendors.show', $this->vendorProfile->slug))
            ->delete(route('vendors.reviews.destroy', [$this->vendorProfile->slug, $review->id]));

        $deleteResponse->assertRedirect(route('vendors.show', $this->vendorProfile->slug));
        $this->assertDatabaseMissing('vendor_reviews', ['id' => $review->id]);
    }
}
