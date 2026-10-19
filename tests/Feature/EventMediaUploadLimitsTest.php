<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventMedia;
use App\Models\User;
use App\Models\VendorProfile;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventMediaUploadLimitsTest extends TestCase
{
    use RefreshDatabase;

    private User $vendorUser;
    private VendorProfile $vendorProfile;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->category = Category::create([
            'name' => 'Concerts & Shows',
            'slug' => 'concerts-shows',
        ]);

        $this->vendorUser = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Starlight Host',
        ]);

        $this->vendorProfile = VendorProfile::create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'Starlight Media House',
            'slug' => 'starlight-media-house',
            'city' => 'Munich',
            'country' => 'Germany',
        ]);
    }

    private function validEventData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Neon Nights Electro Rave',
            'description' => 'Massive laser show and electronic beats.',
            'event_date' => Carbon::now()->addDays(10)->toDateString(),
            'start_time' => '20:00',
            'end_time' => '23:30',
            'category_id' => $this->category->id,
            'audience' => ['Adults'],
            'event_type' => 'free',
            'status' => 'published',
            'venue_name' => 'Cyber Arena',
            'city' => 'Munich',
        ], $overrides);
    }

    public function test_vendor_can_upload_high_res_photo_up_to_30mb()
    {
        // 25MB photo (25600 KB)
        $photo = UploadedFile::fake()->create('high_res_stage.jpg', 25600, 'image/jpeg');

        $data = $this->validEventData([
            'media' => [$photo],
        ]);

        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), $data);
        $response->assertSessionHasNoErrors();

        $event = Event::first();
        $this->assertNotNull($event);
        $this->assertEquals(1, $event->media()->count());

        $media = $event->media()->first();
        $this->assertEquals('image', $media->type);
        $this->assertEquals('high_res_stage.jpg', $media->original_name);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_vendor_can_upload_large_video_up_to_100mb()
    {
        // 60MB video (61440 KB)
        $video = UploadedFile::fake()->create('event_trailer_4k.mp4', 61440, 'video/mp4');

        $data = $this->validEventData([
            'media' => [$video],
        ]);

        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), $data);
        $response->assertSessionHasNoErrors();

        $event = Event::first();
        $this->assertEquals(1, $event->media()->count());

        $media = $event->media()->first();
        $this->assertEquals('video', $media->type);
        $this->assertEquals('event_trailer_4k.mp4', $media->original_name);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_oversized_photo_exceeding_30mb_is_rejected()
    {
        // 32MB photo (32768 KB)
        $photo = UploadedFile::fake()->create('too_large.jpg', 32768, 'image/jpeg');

        $data = $this->validEventData([
            'media' => [$photo],
        ]);

        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), $data);
        $response->assertSessionHasErrors(['media.0']);

        $this->assertEquals(0, Event::count());
    }

    public function test_oversized_video_exceeding_100mb_is_rejected()
    {
        // 105MB video (107520 KB)
        $video = UploadedFile::fake()->create('too_large_video.mp4', 107520, 'video/mp4');

        $data = $this->validEventData([
            'media' => [$video],
        ]);

        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), $data);
        $response->assertSessionHasErrors(['media.0']);

        $this->assertEquals(0, Event::count());
    }

    public function test_unsupported_file_extension_is_rejected()
    {
        $doc = UploadedFile::fake()->create('agenda.pdf', 1024, 'application/pdf');

        $data = $this->validEventData([
            'media' => [$doc],
        ]);

        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), $data);
        $response->assertSessionHasErrors(['media.0']);
    }

    public function test_maximum_15_media_files_allowed_on_event_create()
    {
        // 16 files (exceeds 15)
        $files = [];
        for ($i = 1; $i <= 16; $i++) {
            $files[] = UploadedFile::fake()->create("photo_{$i}.jpg", 500, 'image/jpeg');
        }

        $data = $this->validEventData([
            'media' => $files,
        ]);

        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), $data);
        $response->assertSessionHasErrors(['media']);

        $this->assertEquals(0, Event::count());
    }

    public function test_vendor_can_upload_multiple_photos_and_videos_within_limits()
    {
        $files = [
            UploadedFile::fake()->create('stage1.jpg', 2000, 'image/jpeg'),
            UploadedFile::fake()->create('stage2.png', 3000, 'image/png'),
            UploadedFile::fake()->create('stage3.webp', 1500, 'image/webp'),
            UploadedFile::fake()->create('highlights.mp4', 15000, 'video/mp4'),
        ];

        $data = $this->validEventData([
            'media' => $files,
        ]);

        $response = $this->actingAs($this->vendorUser)->post(route('vendor.events.store'), $data);
        $response->assertSessionHasNoErrors();

        $event = Event::first();
        $this->assertEquals(4, $event->media()->count());
        $this->assertEquals(3, $event->media()->where('type', 'image')->count());
        $this->assertEquals(1, $event->media()->where('type', 'video')->count());
    }

    public function test_total_event_media_slots_enforced_on_event_update()
    {
        $event = Event::create(array_merge($this->validEventData(), [
            'vendor_profile_id' => $this->vendorProfile->id,
            'slug' => 'neon-nights-update-test',
        ]));

        // Attach 12 existing media items
        for ($i = 1; $i <= 12; $i++) {
            EventMedia::create([
                'event_id' => $event->id,
                'disk' => 'public',
                'path' => "events/{$event->id}/existing_{$i}.jpg",
                'type' => 'image',
                'original_name' => "existing_{$i}.jpg",
                'size' => 102400,
            ]);
        }

        $this->assertEquals(12, $event->media()->count());

        // 1. Try to upload 4 more files (12 + 4 = 16 > 15 max) -> fails
        $oversizedBatch = [
            UploadedFile::fake()->create('extra1.jpg', 500, 'image/jpeg'),
            UploadedFile::fake()->create('extra2.jpg', 500, 'image/jpeg'),
            UploadedFile::fake()->create('extra3.jpg', 500, 'image/jpeg'),
            UploadedFile::fake()->create('extra4.jpg', 500, 'image/jpeg'),
        ];

        $failResponse = $this->actingAs($this->vendorUser)->put(route('vendor.events.update', $event), array_merge(
            $this->validEventData(),
            ['media' => $oversizedBatch]
        ));

        $failResponse->assertSessionHasErrors(['media']);
        $this->assertEquals(12, $event->fresh()->media()->count());

        // 2. Upload exactly 3 more files (12 + 3 = 15 <= 15 max) -> succeeds
        $validBatch = [
            UploadedFile::fake()->create('extra1.jpg', 500, 'image/jpeg'),
            UploadedFile::fake()->create('extra2.jpg', 500, 'image/jpeg'),
            UploadedFile::fake()->create('extra3.jpg', 500, 'image/jpeg'),
        ];

        $successResponse = $this->actingAs($this->vendorUser)->put(route('vendor.events.update', $event), array_merge(
            $this->validEventData(),
            ['media' => $validBatch]
        ));

        $successResponse->assertSessionHasNoErrors();
        $this->assertEquals(15, $event->fresh()->media()->count());
    }

    public function test_vendor_can_delete_individual_media_file()
    {
        $event = Event::create(array_merge($this->validEventData(), [
            'vendor_profile_id' => $this->vendorProfile->id,
            'slug' => 'neon-nights-delete-test',
        ]));

        $filePath = "events/{$event->id}/photo_to_delete.jpg";
        Storage::disk('public')->put($filePath, 'dummy content');

        $media = EventMedia::create([
            'event_id' => $event->id,
            'disk' => 'public',
            'path' => $filePath,
            'type' => 'image',
            'original_name' => 'photo_to_delete.jpg',
            'size' => 1024,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($filePath));

        // Delete media
        $response = $this->actingAs($this->vendorUser)
            ->delete(route('vendor.events.media.destroy', [$event->id, $media->id]));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseMissing('event_media', ['id' => $media->id]);
        $this->assertFalse(Storage::disk('public')->exists($filePath));
    }

    public function test_unauthorized_user_cannot_delete_event_media()
    {
        $otherVendorUser = User::factory()->create(['role' => 'vendor']);
        $event = Event::create(array_merge($this->validEventData(), [
            'vendor_profile_id' => $this->vendorProfile->id,
            'slug' => 'forbidden-delete-test',
        ]));

        $media = EventMedia::create([
            'event_id' => $event->id,
            'disk' => 'public',
            'path' => "events/{$event->id}/safe.jpg",
            'type' => 'image',
            'original_name' => 'safe.jpg',
            'size' => 1024,
        ]);

        $response = $this->actingAs($otherVendorUser)
            ->delete(route('vendor.events.media.destroy', [$event->id, $media->id]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('event_media', ['id' => $media->id]);
    }

    public function test_create_and_edit_views_display_updated_limits()
    {
        // 1. Create view displays 15 media and 30MB/100MB
        $createResponse = $this->actingAs($this->vendorUser)->get(route('vendor.events.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('15');
        $createResponse->assertSee('30MB');
        $createResponse->assertSee('100MB');

        // 2. Edit view displays media slots and delete button
        $event = Event::create(array_merge($this->validEventData(), [
            'vendor_profile_id' => $this->vendorProfile->id,
            'slug' => 'view-edit-limits-test',
        ]));

        $editResponse = $this->actingAs($this->vendorUser)->get(route('vendor.events.edit', $event));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('15');
        $editResponse->assertSee('slots available');
    }
}
