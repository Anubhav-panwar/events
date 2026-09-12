<x-app-layout>
    @push('meta')
        <title>Verified Event Organizers &amp; Partners — Huddle</title>
        <meta name="description" content="Discover premier event organizers, music producers, workshop leaders, and festival hosts verified on Huddle.">
    @endpush

    {{-- Directory Hero Header --}}
    <section class="bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 text-white py-16 md:py-20 border-b border-slate-800 relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="app-content relative z-10 max-w-4xl">
            <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30 uppercase tracking-wider">
                Organizer Directory
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight text-white mt-4">
                Verified Event Hosts &amp; Venues
            </h1>
            <p class="text-slate-300 text-base sm:text-lg mt-3 max-w-2xl leading-relaxed">
                Connect with authentic event creators, festival producers, and venue partners curating live experiences across top cities.
            </p>
        </div>
    </section>

    {{-- Vendors Grid Section --}}
    <section class="page-section bg-slate-50 min-h-[600px]">
        <div class="app-content">
            @if($vendors->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($vendors as $vendor)
                        @php
                            $cover = $vendor->media->where('type','image')->first();
                            $eventCount = $vendor->events()->where('status','published')->count();
                            $followerCount = $vendor->followers()->count();
                        @endphp
                        <div class="surface overflow-hidden group hover:shadow-xl transition-all duration-300 flex flex-col justify-between border-slate-200">
                            <div>
                                {{-- Cover Header --}}
                                <div class="h-36 overflow-hidden relative bg-gradient-to-br from-emerald-600 via-teal-700 to-slate-900">
                                    @if($cover)
                                        <img src="{{ $cover->url }}" class="w-full h-full object-cover opacity-60 group-hover:scale-105 transition-transform duration-500" alt="{{ $vendor->business_name }}" loading="lazy">
                                    @else
                                        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=800&q=60')] bg-cover bg-center opacity-30"></div>
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>

                                    @if($vendor->city)
                                        <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-black/50 backdrop-blur-md text-white text-[11px] font-semibold border border-white/15 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                            {{ $vendor->city }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Profile Body --}}
                                <div class="p-6 pt-0 relative">
                                    {{-- Avatar --}}
                                    <div class="flex items-end justify-between -mt-10 mb-4">
                                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg border-3 border-white ring-2 ring-emerald-500/30 shrink-0">
                                            {{ strtoupper(substr($vendor->business_name, 0, 1)) }}
                                        </div>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                            ✓ Verified Host
                                        </span>
                                    </div>

                                    <h3 class="font-bold text-slate-900 text-lg group-hover:text-emerald-700 transition-colors">
                                        <a href="{{ route('vendors.show', $vendor->slug ?: ('vendor-' . $vendor->id)) }}">
                                            {{ $vendor->business_name }}
                                        </a>
                                    </h3>

                                    @if($vendor->description)
                                        <p class="text-xs text-slate-600 line-clamp-2 mt-2 leading-relaxed">
                                            {{ $vendor->description }}
                                        </p>
                                    @endif

                                    @if($vendor->categories->count())
                                        <div class="flex flex-wrap gap-1.5 mt-4">
                                            @foreach($vendor->categories->take(3) as $cat)
                                                <span class="badge text-[10px] bg-slate-100 text-slate-700 border-slate-200">{{ $cat->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Footer Bar --}}
                            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-3 text-xs text-slate-500 font-medium">
                                    <span><strong>{{ $eventCount }}</strong> events</span>
                                    <span>&bull;</span>
                                    <span><strong>{{ $followerCount }}</strong> followers</span>
                                </div>
                                <a href="{{ route('vendors.show', $vendor->slug ?: ('vendor-' . $vendor->id)) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1 group/btn">
                                    <span>View Storefront</span>
                                    <span class="group-hover/btn:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($vendors->hasPages())
                    <div class="mt-8">{{ $vendors->links() }}</div>
                @endif
            @else
                <div class="surface p-16 text-center max-w-lg mx-auto">
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-700 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl">
                        🏢
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">No organizers listed yet</h3>
                    <p class="text-slate-500 text-sm mb-6">Be the first to register your organization and publish events on Huddle.</p>
                    @guest
                        <a href="{{ route('register') }}" class="btn-emerald px-8 py-3 font-bold">Register as an Organizer</a>
                    @endguest
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
