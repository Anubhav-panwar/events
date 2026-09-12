<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventMedia;
use App\Models\VendorProfile;
use App\Repositories\EventRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class EventService
{
    public function __construct(private EventRepository $repo)
    {
    }

    public function createEvent(VendorProfile $vendor, array $data, array $mediaFiles = []): Event
    {
        $event = $this->repo->create($vendor, $data);
        foreach ($mediaFiles as $file) {
            $this->storeMedia($event, $file);
        }
        return $event;
    }

    public function updateEvent(Event $event, array $data, array $mediaFiles = []): Event
    {
        $event = $this->repo->update($event, $data);
        foreach ($mediaFiles as $file) {
            $this->storeMedia($event, $file);
        }
        return $event;
    }

    public function storeMedia(Event $event, UploadedFile $file): EventMedia
    {
        $path = Storage::disk('public')->put("events/{$event->id}", $file);

        // Mirror file directly to public/storage for direct web server static serving (cPanel / shared hosting)
        try {
            $publicTarget = public_path('storage/' . $path);
            File::ensureDirectoryExists(dirname($publicTarget));
            $storageSource = storage_path('app/public/' . $path);
            if (file_exists($storageSource) && !file_exists($publicTarget)) {
                @copy($storageSource, $publicTarget);
            }
        } catch (\Throwable $e) {
            // Fallback gracefully if permissions or symlink already exists
        }

        return $event->media()->create([
            'disk' => 'public',
            'path' => $path,
            'type' => str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image',
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);
    }
}
