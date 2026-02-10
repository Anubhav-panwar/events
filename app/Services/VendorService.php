<?php

namespace App\Services;

use App\Models\User;
use App\Models\VendorProfile;
use App\Repositories\VendorRepository;

class VendorService
{
    public function __construct(private VendorRepository $repo)
    {
    }

    public function getOrCreateProfile(User $user, array $data = []): VendorProfile
    {
        return $this->repo->createOrUpdate($user, $data);
    }

    public function getApprovedList(int $perPage = 12)
    {
        return $this->repo->findApprovedPaginated($perPage);
    }
}
