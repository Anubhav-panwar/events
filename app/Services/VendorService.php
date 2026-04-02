<?php

namespace App\Services;

use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorMedia;
use App\Repositories\VendorRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VendorService
{
    public function __construct(private VendorRepository $repo)
    {
    }

    public function getOrCreateProfile(User $user, array $data = [], array $mediaFiles = []): VendorProfile
    {
        if (isset($data['opening_hours']) && is_array($data['opening_hours'])) {
            $data['opening_hours'] = array_values(array_filter($data['opening_hours'], function ($row) {
                if (!is_array($row) || empty($row['day'])) {
                    return false;
                }
                return !empty($row['closed']) || (!empty($row['open']) && !empty($row['close']));
            }));
        }

        $profile = $this->repo->createOrUpdate($user, $data);
        foreach ($mediaFiles as $file) {
            $this->storeMedia($profile, $file);
        }

        return $profile;
    }

    public function getApprovedList(int $perPage = 12)
    {
        return $this->repo->findApprovedPaginated($perPage);
    }

    public function storeMedia(VendorProfile $profile, UploadedFile $file): VendorMedia
    {
        $path = Storage::disk('public')->put("vendors/{$profile->id}", $file);

        return $profile->media()->create([
            'disk' => 'public',
            'path' => $path,
            'type' => str_starts_with((string) $file->getMimeType(), 'video') ? 'video' : 'image',
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);
    }
}
