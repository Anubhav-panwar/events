<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\VendorProfile;

class VendorRepository
{
    public function findApprovedPaginated(int $perPage = 12)
    {
        return VendorProfile::query()
            ->where('is_approved', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->with(['categories', 'media'])
            ->paginate($perPage);
    }

    public function findByUser(User $user): ?VendorProfile
    {
        return VendorProfile::firstWhere('user_id', $user->id);
    }

    public function createOrUpdate(User $user, array $data): VendorProfile
    {
        $categoryIds = $data['category_ids'] ?? null;
        unset($data['category_ids'], $data['media']);

        $profile = VendorProfile::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        if (is_array($categoryIds)) {
            $profile->categories()->sync($categoryIds);
        }

        return $profile;
    }
}
