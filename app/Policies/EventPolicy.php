<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function view(?User $user, Event $event): bool
    {
        return $event->status === 'published'
            || ($user && ($user->isAdmin() || $event->vendorProfile?->user_id === $user->id));
    }

    public function create(User $user): bool
    {
        return $user->isVendor();
    }

    public function update(User $user, Event $event): bool
    {
        return $user->isAdmin() || $event->vendorProfile?->user_id === $user->id;
    }
}
