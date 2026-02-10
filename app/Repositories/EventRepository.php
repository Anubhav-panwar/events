<?php

namespace App\Repositories;

use App\Models\Event;
use App\Models\VendorProfile;

class EventRepository
{
    public function create(VendorProfile $vendor, array $data): Event
    {
        $data['vendor_profile_id'] = $vendor->id;
        return Event::create($data);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);
        return $event;
    }

    public function findBySlug(string $slug): ?Event
    {
        return Event::with(['vendorProfile', 'media', 'ticketTypes', 'category'])->firstWhere('slug', $slug);
    }
}
