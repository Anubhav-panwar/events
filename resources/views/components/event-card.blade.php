@props(['event'])
@php
    $image = $event->media->first();
    $fallbackImages = [
        'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1464375117522-1311d6a5b81f?auto=format&fit=crop&w=1200&q=80',
    ];
    $fallbackIndex = abs(crc32((string) ($event->slug ?? $event->id ?? $event->title ?? 'huddle'))) % count($fallbackImages);
    $imgUrl = $image ? Storage::disk($image->disk)->url($image->path) : $fallbackImages[$fallbackIndex];
    $price = $event->ticketTypes->first()?->price ?? null;
    $formattedPrice = $price ? '$' . number_format($price, 2) : 'Free';
@endphp

<div class="group">
    <div class="relative card card-hover">
        <a href="{{ route('events.show', $event->slug) }}" class="block">
            <div class="relative overflow-hidden h-48">
                <img src="{{ $imgUrl }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="{{ $event->title }}">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

                <div class="absolute top-3 right-3">
                    @if($price === null || $price == 0)
                        <span class="px-3 py-1 bg-emerald-500 text-white text-xs font-bold rounded-full shadow-lg">FREE</span>
                    @else
                        <span class="px-3 py-1 bg-white/90 backdrop-blur-sm text-gray-900 text-xs font-bold rounded-full shadow-lg">{{ $formattedPrice }}</span>
                    @endif
                </div>

                <div class="absolute bottom-3 left-3 right-3">
                    <div class="flex items-center gap-2 text-white text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">{{ $event->event_date->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 via-teal-500 to-sky-500 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($event->vendorProfile?->business_name ?? 'E', 0, 1)) }}
                    </div>
                    <span class="text-sm text-slate-600 font-medium">{{ $event->vendorProfile?->business_name }}</span>
                </div>

                <h3 class="font-semibold text-lg text-slate-900 mb-2 line-clamp-2 group-hover:text-emerald-700 transition-colors">
                    {{ $event->title }}
                </h3>

                @if($event->address)
                <div class="flex items-start gap-2 text-sm text-gray-600 mb-4">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="line-clamp-1">{{ \Illuminate\Support\Str::limit($event->address, 45) }}</span>
                </div>
                @endif

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-gray-600">{{ $event->start_time instanceof \Carbon\Carbon ? $event->start_time->format('g:i A') : $event->start_time }}</span>
                    </div>
                    <div class="text-emerald-700 font-semibold text-sm group-hover:text-emerald-800 flex items-center gap-1">
                        View Details
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
