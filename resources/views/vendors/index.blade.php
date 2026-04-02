<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Vendors & Organisers</h2>
            <p class="page-subtitle">Connect with trusted event organisers and venue owners in your area.</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content">
            @if($vendors->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($vendors as $vendor)
                        @php
                            $cover = $vendor->media->where('type','image')->first();
                            $eventCount = $vendor->events()->where('status','published')->count();
                            $followerCount = $vendor->followers()->count();
                        @endphp
                        <div class="card group card-hover">
                            {{-- Cover --}}
                            <div class="h-40 overflow-hidden relative bg-gradient-to-br from-emerald-400 via-teal-500 to-sky-500">
                                @if($cover)
                                    <img src="{{ Storage::disk($cover->disk)->url($cover->path) }}" class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-500" alt="">
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center opacity-30">
                                        <svg class="w-24 h-24 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            </div>

                            <div class="p-5">
                                <div class="flex items-start gap-3 -mt-10 mb-3 relative">
                                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-400 via-teal-400 to-sky-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold shadow-lg border-3 border-white shrink-0">
                                        {{ strtoupper(substr($vendor->business_name, 0, 1)) }}
                                    </div>
                                    <div class="pt-8">
                                        <a href="{{ route('vendors.show', $vendor->slug) }}" class="font-bold text-slate-900 group-hover:text-emerald-700 transition-colors text-lg leading-tight hover:underline">
                                            {{ $vendor->business_name }}
                                        </a>
                                        @if($vendor->city)
                                            <p class="text-sm text-slate-500 flex items-center gap-1 mt-0.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                                {{ $vendor->city }}{{ $vendor->country ? ', '.$vendor->country : '' }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                @if($vendor->description)
                                    <p class="text-sm text-slate-600 line-clamp-2 mb-4">{{ $vendor->description }}</p>
                                @endif

                                @if($vendor->categories->count())
                                    <div class="flex flex-wrap gap-1.5 mb-4">
                                        @foreach($vendor->categories->take(3) as $cat)
                                            <span class="badge text-xs">{{ $cat->name }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                                    <div class="flex gap-4 text-xs text-slate-500">
                                        <span>{{ $eventCount }} events</span>
                                        <span>{{ $followerCount }} followers</span>
                                    </div>
                                    <a href="{{ route('vendors.show', $vendor->slug) }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                                        View Profile →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-8">{{ $vendors->links() }}</div>
            @else
                <div class="surface p-16 text-center max-w-lg mx-auto">
                    <div class="text-6xl mb-5">🏢</div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">No vendors yet</h3>
                    <p class="text-slate-600 mb-6">Be the first to register your business as a vendor on Huddle.</p>
                    @guest
                        <a href="{{ route('register') }}" class="btn-primary px-8 py-3">Register as Vendor</a>
                    @endguest
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
