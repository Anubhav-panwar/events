@props(['event'])
@php
    $image = $event->media->where('type', 'image')->first();
    $imageCount = $event->media->where('type', 'image')->count();
    $fallbackImages = [
        'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80',
    ];
    $fallbackIndex = abs(crc32((string) ($event->slug ?? $event->id ?? $event->title ?? 'event'))) % count($fallbackImages);
    $imgUrl = $image ? $image->url : $fallbackImages[$fallbackIndex];

    $price = $event->base_price;
    if ($price === null && $event->relationLoaded('ticketTypes')) {
        $price = optional($event->ticketTypes->sortBy('price')->first())->price;
    }

    $isSaved = (bool) ($event->is_saved ?? false);
@endphp

<div class="group">
    <div class="relative card card-hover">
        <a href="{{ route('events.show', $event->slug) }}" class="block">
            <div class="relative overflow-hidden h-48 bg-slate-100">
                <img src="{{ $imgUrl }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $event->title }}" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                @if($imageCount > 1)
                    <div class="absolute top-3 left-3">
                        <span class="px-2.5 py-1 bg-slate-900/75 backdrop-blur-sm text-white text-[11px] font-bold rounded-full shadow flex items-center gap-1">
                            <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $imageCount }} photos
                        </span>
                    </div>
                @endif

                <div class="absolute top-3 right-3">
                    @if($price === null || (float) $price == 0)
                        <span class="px-3 py-1 bg-emerald-600 text-white text-xs font-bold rounded-full shadow-lg">FREE</span>
                    @else
                        <span class="px-3 py-1 bg-white/95 backdrop-blur-sm text-slate-900 text-xs font-bold rounded-full shadow-lg">${{ number_format((float) $price, 2) }}</span>
                    @endif
                </div>

                <div class="absolute bottom-3 left-3 right-3">
                    <div class="flex items-center gap-2 text-white text-sm">
                        <span class="font-medium">{{ $event->event_date?->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </a>

        <div class="p-5">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 via-teal-500 to-sky-500 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($event->vendorProfile?->business_name ?? 'E', 0, 1)) }}
                    </div>
                    <span class="text-sm text-slate-600 font-medium">{{ $event->vendorProfile?->business_name }}</span>
                </div>

                @auth
                    @if($isSaved)
                        <form method="POST" action="{{ route('events.unsave', $event->slug) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">Saved</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('events.save', $event->slug) }}">
                            @csrf
                            <button class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200">Save</button>
                        </form>
                    @endif
                @endauth
            </div>

            <a href="{{ route('events.show', $event->slug) }}" class="font-semibold text-lg text-slate-900 mb-2 line-clamp-2 group-hover:text-emerald-700 transition-colors block">
                {{ $event->title }}
            </a>

            @if($event->address)
                <div class="text-sm text-gray-600 mb-4 line-clamp-1">{{ \Illuminate\Support\Str::limit($event->address, 60) }}</div>
            @endif

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <span class="text-sm text-gray-600">{{ $event->start_time instanceof \Carbon\Carbon ? $event->start_time->format('g:i A') : $event->start_time }}</span>
                <a href="{{ route('events.show', $event->slug) }}" class="text-emerald-700 font-semibold text-sm">View Details</a>
            </div>
        </div>
    </div>
</div>
