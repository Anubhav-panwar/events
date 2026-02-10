<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VendorProfile;

class VendorProfilePolicy
{
    public function view(?User $user, VendorProfile $profile): bool
    {
        return $profile->is_approved || ($user && ($user->isAdmin() || $profile->user_id === $user->id));
    }

    public function update(User $user, VendorProfile $profile): bool
    {
        return $user->isAdmin() || $profile->user_id === $user->id;
    }
}
