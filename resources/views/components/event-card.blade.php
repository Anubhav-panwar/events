@props(['event'])
<div class="card overflow-hidden">
    @php
        $image = $event->media->first();
        $imgUrl = $image ? Storage::disk($image->disk)->url($image->path) : 'https://images.unsplash.com/photo-1540574163026-643ea20ade25?q=80&w=1640&auto=format&fit=crop';
    @endphp
    <img src="{{ $imgUrl }}" class="w-full h-40 object-cover" alt="{{ $event->title }}">
    <div class="p-4">
        <div class="text-sm text-gray-500 flex items-center justify-between">
            <span>{{ $event->vendorProfile?->business_name }}</span>
            <span>{{ $event->event_date->format('M d') }}</span>
        </div>
        <a href="{{ route('events.show', $event->slug) }}" class="block font-semibold mt-1 text-blue-700">{{ $event->title }}</a>
        <div class="text-sm text-gray-600 mt-1">{{ \Illuminate\Support\Str::limit($event->address, 40) }}</div>
        <div class="flex items-center justify-between mt-3">
            <a class="text-sm text-blue-600" href="{{ route('events.show', $event->slug) }}">View Details</a>
            <span class="text-sm text-gray-500">{{ $event->ticketTypes->first()?->price ? number_format($event->ticketTypes->first()->price, 2) : 'Free' }}</span>
        </div>
    </div>
</div>
