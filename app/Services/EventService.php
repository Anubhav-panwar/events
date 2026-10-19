<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventMedia;
use App\Models\VendorProfile;
use App\Repositories\EventRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventService
{
    public function __construct(
        private EventRepository $repo,
        private ?EventNotificationService $notificationService = null
    ) {
        $this->notificationService = $notificationService ?? app(EventNotificationService::class);
    }

    public function createEvent(VendorProfile $vendor, array $data, array $mediaFiles = []): Event
    {
        $event = $this->repo->create($vendor, $data);
        foreach ($mediaFiles as $file) {
            $this->storeMedia($event, $file);
        }

        // If initially created with confirmed dates and published, mark as announced to avoid later spurious triggers
        if ($event->status === 'published' && $event->isDateConfirmed()) {
            $event->updateQuietly(['dates_announced_at' => now()]);
        }

        return $event;
    }

    public function updateEvent(Event $event, array $data, array $mediaFiles = []): Event
    {
        $correlationId = (string) Str::uuid();
        $wasAnnounced = !empty($event->dates_announced_at);

        Log::info('Vendor event update received', [
            'event_id' => $event->id,
            'correlation_id' => $correlationId,
            'was_announced' => $wasAnnounced,
        ]);

        $event = $this->repo->update($event, $data);
        foreach ($mediaFiles as $file) {
            $this->storeMedia($event, $file);
        }

        $event->refresh();

        Log::info('Vendor event saved', [
            'event_id' => $event->id,
            'correlation_id' => $correlationId,
            'status' => $event->status,
            'date_confirmed' => $event->isDateConfirmed(),
            'has_event_date' => !empty($event->event_date),
            'has_start_time' => !empty($event->start_time),
            'booking_eligible' => $event->isBookingEligible(),
        ]);

        // Trigger date announcement emails if dates were unannounced and are now confirmed & published
        if (!$wasAnnounced && $event->status === 'published' && $event->isDateConfirmed()) {
            Log::info('Vendor event update triggers date announcement', [
                'event_id' => $event->id,
                'correlation_id' => $correlationId,
                'reason' => 'first_confirmed_published_save',
            ]);
            $this->notificationService->notifySubscribersOfAnnouncedDates($event, $correlationId);
        } else {
            $reason = $wasAnnounced
                ? 'already_announced'
                : ($event->status !== 'published' ? 'event_not_published' : 'schedule_not_confirmed');
            Log::info('Vendor event update skipped date announcement', [
                'event_id' => $event->id,
                'correlation_id' => $correlationId,
                'reason' => $reason,
            ]);
        }

        return $event;
    }

    public function storeMedia(Event $event, UploadedFile $file): EventMedia
    {
        $path = Storage::disk('public')->put("events/{$event->id}", $file);
        $storageSource = storage_path('app/public/' . $path);

        $isImage = str_starts_with($file->getMimeType(), 'image/');
        if ($isImage) {
            $this->optimizeImageIfPossible($storageSource, $file->getClientOriginalExtension());
        }

        // Mirror file directly to public/storage for direct web server static serving (cPanel / shared hosting)
        try {
            $publicTarget = public_path('storage/' . $path);
            File::ensureDirectoryExists(dirname($publicTarget));
            if (file_exists($storageSource) && !file_exists($publicTarget)) {
                @copy($storageSource, $publicTarget);
            }
        } catch (\Throwable $e) {
            // Fallback gracefully if permissions or symlink already exists
        }

        $finalSize = file_exists($storageSource) ? (int) filesize($storageSource) : (int) $file->getSize();

        return $event->media()->create([
            'disk' => 'public',
            'path' => $path,
            'type' => str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image',
            'original_name' => $file->getClientOriginalName(),
            'size' => $finalSize,
        ]);
    }

    public function deleteMedia(Event $event, EventMedia $media): bool
    {
        if ((int) $media->event_id !== (int) $event->id) {
            return false;
        }

        try {
            Storage::disk($media->disk ?: 'public')->delete($media->path);
            $publicTarget = public_path('storage/' . $media->path);
            if (file_exists($publicTarget)) {
                @unlink($publicTarget);
            }
        } catch (\Throwable $e) {
            // Ignore file deletion error
        }

        return (bool) $media->delete();
    }

    public function optimizeImageIfPossible(string $filePath, ?string $extension = null): void
    {
        if (!extension_loaded('gd') || !file_exists($filePath)) {
            return;
        }

        try {
            $content = @file_get_contents($filePath);
            if (!$content) {
                return;
            }

            $img = @imagecreatefromstring($content);
            if (!$img) {
                return;
            }

            // 1. Auto-orient EXIF orientation for JPEGs
            $ext = strtolower((string) ($extension ?: pathinfo($filePath, PATHINFO_EXTENSION)));
            if (in_array($ext, ['jpg', 'jpeg'], true) && function_exists('exif_read_data')) {
                $exif = @exif_read_data($filePath);
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $rotated = @imagerotate($img, 180, 0);
                            if ($rotated) { imagedestroy($img); $img = $rotated; }
                            break;
                        case 6:
                            $rotated = @imagerotate($img, -90, 0);
                            if ($rotated) { imagedestroy($img); $img = $rotated; }
                            break;
                        case 8:
                            $rotated = @imagerotate($img, 90, 0);
                            if ($rotated) { imagedestroy($img); $img = $rotated; }
                            break;
                    }
                }
            }

            // 2. Downscale huge dimensions if larger than 2560px
            $width = imagesx($img);
            $height = imagesy($img);
            $maxDim = 2560;

            if ($width > $maxDim || $height > $maxDim) {
                $ratio = min($maxDim / $width, $maxDim / $height);
                $newW = max(1, (int) round($width * $ratio));
                $newH = max(1, (int) round($height * $ratio));

                $target = imagecreatetruecolor($newW, $newH);

                // Preserve alpha channel
                imagealphablending($target, false);
                imagesavealpha($target, true);
                $transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
                imagefilledrectangle($target, 0, 0, $newW, $newH, $transparent);

                imagecopyresampled($target, $img, 0, 0, 0, 0, $newW, $newH, $width, $height);
                imagedestroy($img);
                $img = $target;
            }

            // 3. Save optimized image in place
            if ($ext === 'png') {
                imagepng($img, $filePath, 7);
            } elseif ($ext === 'webp' && function_exists('imagewebp')) {
                imagewebp($img, $filePath, 88);
            } else {
                imagejpeg($img, $filePath, 88);
            }

            imagedestroy($img);
            clearstatcache(true, $filePath);
        } catch (\Throwable $e) {
            // Keep original if optimization fails
        }
    }
}
