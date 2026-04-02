<x-app-layout>
    @php
        $coverImage = $vendor->media->where('type', 'image')->first();
        $allImages = $vendor->media->where('type', 'image');
        $allVideos = $vendor->media->where('type', 'video');
        $followerCount = $vendor->followers()->count();
        $eventCount = $vendor->events()->where('status', 'published')->count();
        $isFollowing = auth()->check()
            ? auth()->user()->followedVendors()->where('vendor_profile_id', $vendor->id)->exists()
            : false;
    @endphp

    @push('meta')
        <title>{{ $vendor->business_name }} — Huddle Vendor</title>
        <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($vendor->description), 160) }}">
    @endpush

    {{-- Hero Banner --}}
    <div class="relative h-60 md:h-80 overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-sky-600">
        @if($coverImage)
            <img src="{{ Storage::disk($coverImage->disk)->url($coverImage->path) }}" class="absolute inset-0 w-full h-full object-cover opacity-40" alt="">
        @else
            <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1600&q=60')] bg-cover bg-center opacity-25"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 to-transparent"></div>
        <div class="relative app-content h-full flex items-end pb-8">
            <div class="flex items-end gap-5">
                <div class="w-20 h-20 md:w-24 md:h-24 bg-gradient-to-br from-emerald-400 via-teal-400 to-sky-500 rounded-2xl flex items-center justify-center text-white text-3xl font-bold shadow-2xl border-4 border-white/30">
                    {{ strtoupper(substr($vendor->business_name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl md:text-4xl font-bold text-white">{{ $vendor->business_name }}</h1>
                    <p class="text-emerald-200 text-sm mt-1 flex items-center gap-2">
                        @if($vendor->city)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $vendor->city }}{{ $vendor->country ? ', '.$vendor->country : '' }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <section class="page-section">
        <div class="app-content">
            {{-- Stats + Follow Bar --}}
            <div class="surface p-5 flex flex-wrap items-center justify-between gap-4 mb-8">
                <div class="flex gap-6">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-slate-900">{{ $followerCount }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Followers</p>
                    </div>
                    <div class="w-px bg-slate-200"></div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-slate-900">{{ $eventCount }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Events</p>
                    </div>
                    @if($vendor->categories->count())
                        <div class="w-px bg-slate-200"></div>
                        <div class="flex flex-wrap gap-2 items-center">
                            @foreach($vendor->categories as $cat)
                                <span class="badge">{{ $cat->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    @if($vendor->website)
                        <a href="{{ $vendor->website }}" target="_blank" rel="noopener" class="btn-secondary text-sm px-4 py-2">
                            <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Website
                        </a>
                    @endif
                    @auth
                        @if($isFollowing)
                            <form method="POST" action="{{ route('vendors.unfollow', $vendor->slug) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn-secondary text-sm px-5 py-2">
                                    <svg class="w-4 h-4 mr-1.5 inline" fill="currentColor" viewBox="0 0 24 24"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                    Following
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('vendors.follow', $vendor->slug) }}">
                                @csrf
                                <button class="btn-primary text-sm px-5 py-2">
                                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                    Follow Vendor
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-primary text-sm px-5 py-2">Follow Vendor</a>
                    @endauth
                </div>
            </div>

            {{-- Main Content Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left: About + Details --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- About --}}
                    <div class="surface p-6">
                        <h2 class="text-lg font-bold text-slate-900 mb-3">About {{ $vendor->business_name }}</h2>
                        <p class="text-slate-700 leading-relaxed">{{ $vendor->description ?: 'No description provided yet.' }}</p>
                    </div>

                    {{-- Gallery --}}
                    @if($vendor->media->count())
                        <div class="surface p-6">
                            <h2 class="text-lg font-bold text-slate-900 mb-4">Gallery</h2>
                            @if($allImages->count())
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
                                    @foreach($allImages->take(6) as $m)
                                        <div class="media-tile aspect-square cursor-pointer" onclick="openLightbox('{{ Storage::disk($m->disk)->url($m->path) }}')">
                                            <img src="{{ Storage::disk($m->disk)->url($m->path) }}" alt="{{ $m->original_name ?? 'Gallery image' }}" loading="lazy">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if($allVideos->count())
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($allVideos as $m)
                                        <video class="rounded-xl border border-slate-200 w-full" controls src="{{ Storage::disk($m->disk)->url($m->path) }}"></video>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Map --}}
                    @if($vendor->latitude && $vendor->longitude)
                        <div class="surface p-6">
                            <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                Location
                            </h2>
                            <p class="text-sm text-slate-600 mb-3">{{ $vendor->address }}{{ $vendor->city ? ', '.$vendor->city : '' }}{{ $vendor->country ? ', '.$vendor->country : '' }}</p>
                            <iframe
                                class="w-full h-64 rounded-xl border border-slate-200"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                src="https://maps.google.com/maps?q={{ $vendor->latitude }},{{ $vendor->longitude }}&z=15&output=embed">
                            </iframe>
                        </div>
                    @elseif($vendor->address)
                        <div class="surface p-6">
                            <h2 class="text-lg font-bold text-slate-900 mb-2">Address</h2>
                            <p class="text-slate-700">{{ $vendor->address }}{{ $vendor->city ? ', '.$vendor->city : '' }}{{ $vendor->country ? ', '.$vendor->country : '' }}</p>
                        </div>
                    @endif
                </div>

                {{-- Right Sidebar --}}
                <div class="space-y-6">
                    {{-- Opening Hours --}}
                    @if($vendor->opening_hours && count($vendor->opening_hours))
                        <div class="surface p-6">
                            <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Opening Hours
                            </h3>
                            <div class="space-y-2">
                                @php $today = now()->format('l'); @endphp
                                @foreach($vendor->opening_hours as $row)
                                    <div class="flex justify-between items-center text-sm {{ ($row['day'] ?? '') === $today ? 'font-semibold text-emerald-700' : 'text-slate-700' }}">
                                        <span>{{ $row['day'] ?? 'Day' }}</span>
                                        <span>
                                            @if(!empty($row['closed']))
                                                <span class="text-red-500 text-xs font-medium">Closed</span>
                                            @else
                                                {{ $row['open'] ?? '-' }} – {{ $row['close'] ?? '-' }}
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Contact / Social --}}
                    <div class="surface p-6">
                        <h3 class="font-bold text-slate-900 mb-4">Connect</h3>
                        <div class="space-y-3">
                            @if($vendor->phone)
                                <a href="tel:{{ $vendor->phone }}" class="flex items-center gap-3 text-sm text-slate-700 hover:text-emerald-700 transition-colors">
                                    <span class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center shrink-0">📞</span>
                                    {{ $vendor->phone }}
                                </a>
                            @endif
                            @if($vendor->website)
                                <a href="{{ $vendor->website }}" target="_blank" rel="noopener" class="flex items-center gap-3 text-sm text-slate-700 hover:text-emerald-700 transition-colors">
                                    <span class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center shrink-0">🌐</span>
                                    Website
                                </a>
                            @endif
                            @if($vendor->instagram)
                                <a href="{{ $vendor->instagram }}" target="_blank" rel="noopener" class="flex items-center gap-3 text-sm text-slate-700 hover:text-pink-600 transition-colors">
                                    <span class="w-8 h-8 bg-pink-50 rounded-lg flex items-center justify-center shrink-0">📸</span>
                                    Instagram
                                </a>
                            @endif
                            @if($vendor->facebook)
                                <a href="{{ $vendor->facebook }}" target="_blank" rel="noopener" class="flex items-center gap-3 text-sm text-slate-700 hover:text-blue-600 transition-colors">
                                    <span class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center shrink-0">👥</span>
                                    Facebook
                                </a>
                            @endif
                            @if($vendor->twitter)
                                <a href="{{ $vendor->twitter }}" target="_blank" rel="noopener" class="flex items-center gap-3 text-sm text-slate-700 hover:text-slate-900 transition-colors">
                                    <span class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center shrink-0">𝕏</span>
                                    X / Twitter
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upcoming Events --}}
            <div class="mt-10">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-slate-900">Upcoming Events</h2>
                    <a href="{{ route('events.index') }}" class="text-sm text-emerald-700 hover:underline">See all events →</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @forelse($events as $event)
                        <x-event-card :event="$event" />
                    @empty
                        <div class="md:col-span-3 surface p-10 text-center">
                            <div class="text-5xl mb-3">📅</div>
                            <p class="text-slate-600">No upcoming events from this vendor yet.</p>
                        </div>
                    @endforelse
                </div>
                @if($events->hasPages())
                    <div class="mt-6">{{ $events->links() }}</div>
                @endif
            </div>
        </div>
    </section>

    {{-- Lightbox --}}
    <div id="lightbox" class="fixed inset-0 bg-black/90 z-[200] hidden flex items-center justify-center p-4" onclick="closeLightbox()">
        <img id="lightboxImg" src="" class="max-h-[90vh] max-w-full rounded-xl object-contain" alt="">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white bg-white/20 rounded-full p-2 hover:bg-white/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    @push('scripts')
    <script>
        function openLightbox(src) {
            document.getElementById('lightboxImg').src = src;
            document.getElementById('lightbox').classList.remove('hidden');
            document.getElementById('lightbox').classList.add('flex');
        }
        function closeLightbox() {
            document.getElementById('lightbox').classList.add('hidden');
            document.getElementById('lightbox').classList.remove('flex');
        }
    </script>
    @endpush
</x-app-layout>
