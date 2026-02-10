<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\VendorProfile;

class VendorRepository
{
    public function findApprovedPaginated(int $perPage = 12)
    {
        return VendorProfile::query()->where('is_approved', true)->with('categories')->paginate($perPage);
    }

    public function findByUser(User $user): ?VendorProfile
    {
        return VendorProfile::firstWhere('user_id', $user->id);
    }

    public function createOrUpdate(User $user, array $data): VendorProfile
    {
        $profile = VendorProfile::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );
        if (isset($data['category_ids'])) {
            $profile->categories()->sync($data['category_ids']);
        }
        return $profile;
    }
}
