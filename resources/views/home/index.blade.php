<x-app-layout>
    {{-- Hero Section --}}
    <section class="relative bg-white pt-16 pb-24 md:pt-24 md:pb-32 border-b border-slate-200">
        <div class="app-content relative z-10">
            <div class="grid items-center gap-16 lg:grid-cols-2">
                <div>
                    <span class="brand-pill mb-6">Your Local Event Hub</span>
                    <h1 class="text-4xl md:text-6xl font-bold leading-tight text-slate-900 tracking-tight">
                        Discover Events. <br/>
                        <span class="text-emerald-700">Book Faster. Enjoy More.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg text-slate-600 leading-relaxed">
                        Find events around you or plan ahead for your next trip. Save favourites, share with friends, and get your tickets — all in one place.
                    </p>

                    {{-- Inline Search Bar --}}
                    <form action="{{ route('search') }}" method="GET" class="mt-10 flex flex-col sm:flex-row gap-3 max-w-xl bg-slate-50 p-3 rounded-lg border border-slate-200 shadow-sm">
                        <div class="relative flex-1">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" name="q" placeholder="What are you looking for?" class="input-field pl-11 shadow-none border-transparent bg-transparent" style="box-shadow: none;">
                        </div>
                        <div class="relative w-full sm:w-1/3 border-t sm:border-t-0 sm:border-l border-slate-200 pt-3 sm:pt-0">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <input type="text" name="place" placeholder="City" class="input-field pl-11 shadow-none border-transparent bg-transparent" style="box-shadow: none;">
                        </div>
                        <button type="submit" class="btn-primary shrink-0 w-full sm:w-auto mt-3 sm:mt-0">Search</button>
                    </form>

                    <div class="mt-8 flex items-center gap-6">
                        <a href="{{ route('events.index') }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-800 underline underline-offset-4">Browse all events &rarr;</a>
                        <a href="{{ route('vendors.index') }}" class="text-sm font-bold text-slate-600 hover:text-slate-900 border-b border-transparent hover:border-slate-900 transition-all">Find vendors</a>
                    </div>
                </div>

                {{-- Hero Visual (strict image block, no floating shapes) --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div class="media-tile aspect-[4/5]">
                            <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=800&q=80" alt="Event audience" loading="lazy">
                        </div>
                    </div>
                    <div class="space-y-4 pt-12">
                        <div class="media-tile aspect-[4/5]">
                            <img src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=800&q=80" alt="Networking event" loading="lazy">
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Stats Strip --}}
            <div class="mt-20 pt-10 border-t border-slate-200 grid grid-cols-2 gap-8 sm:grid-cols-4">
                <div>
                    <p class="text-3xl font-bold text-slate-900">{{ number_format($stats['events']) }}+</p>
                    <p class="text-sm font-medium text-slate-500 mt-1 uppercase tracking-wider">Events</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-slate-900">{{ number_format($stats['vendors']) }}+</p>
                    <p class="text-sm font-medium text-slate-500 mt-1 uppercase tracking-wider">Vendors</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-slate-900">{{ number_format($stats['cities']) }}+</p>
                    <p class="text-sm font-medium text-slate-500 mt-1 uppercase tracking-wider">Cities</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-slate-900">4.9/5</p>
                    <p class="text-sm font-medium text-slate-500 mt-1 uppercase tracking-wider">Rating</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Category Quick Links --}}
    <section class="py-8 bg-slate-50 border-b border-slate-200">
        <div class="app-content">
            <div class="flex items-center gap-3 overflow-x-auto pb-4 hide-scrollbar">
                <a href="{{ route('events.index') }}" class="btn-slate bg-slate-900 text-white font-bold px-5 py-2.5 rounded shrink-0 flex items-center gap-2 hover:bg-slate-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    All Events
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('events.index', ['category_id' => $cat->id]) }}" class="px-5 py-2.5 rounded border border-slate-200 bg-white text-slate-700 font-bold text-sm shrink-0 hover:border-slate-400 hover:text-slate-900 shadow-sm transition-all">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Trending Events --}}
    <section class="section bg-white border-b border-slate-200">
        <div class="app-content">
            <div class="flex items-end justify-between mb-10">
                <div>
                    <h2 class="section-header mb-1">Trending Events</h2>
                    <p class="section-subtitle">Popular picks this week near you</p>
                </div>
                <a href="{{ route('events.index') }}" class="btn-secondary hidden sm:inline-flex">View All</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($upcoming as $event)
                    <x-event-card :event="$event" />
                @empty
                    <div class="col-span-3 surface border-dashed border-2 bg-slate-50 p-12 text-center shadow-none">
                        <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <h3 class="text-lg font-bold text-slate-900">No upcoming events</h3>
                        <p class="text-slate-500 mt-1">Check back soon for new listings.</p>
                        @auth
                            @if(auth()->user()->isVendor())
                                <a href="{{ route('vendor.events.create') }}" class="btn-primary mt-6 inline-flex">Create an Event</a>
                            @endif
                        @endauth
                    </div>
                @endforelse
            </div>
            <div class="mt-8 text-center sm:hidden">
                <a href="{{ route('events.index') }}" class="btn-secondary">View All Events</a>
            </div>
        </div>
    </section>

    {{-- How It Works --}}
    <section class="section bg-slate-50 border-b border-slate-200">
        <div class="app-content">
            <div class="text-center mb-16">
                <h2 class="section-header">How Huddle Works</h2>
                <p class="section-subtitle">A seamless experience for both attendees and organizers.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
                {{-- For Attendees --}}
                <div class="surface p-8 sm:p-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 bg-slate-900 text-white rounded flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900">For Attendees</h3>
                    </div>
                    <div class="space-y-8">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded border border-slate-200 bg-white flex items-center justify-center text-slate-700 shrink-0 shadow-sm font-bold">1</div>
                            <div>
                                <p class="font-bold text-slate-900 text-lg">Search &amp; Explore</p>
                                <p class="text-slate-600 mt-1">Find events near you or at your next destination using location search and smart filters.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded border border-slate-200 bg-white flex items-center justify-center text-slate-700 shrink-0 shadow-sm font-bold">2</div>
                            <div>
                                <p class="font-bold text-slate-900 text-lg">Save &amp; Share</p>
                                <p class="text-slate-600 mt-1">Bookmark events to your account, add them to your phone calendar, and share with friends.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded border border-slate-200 bg-white flex items-center justify-center text-slate-700 shrink-0 shadow-sm font-bold">3</div>
                            <div>
                                <p class="font-bold text-slate-900 text-lg">Book &amp; Attend</p>
                                <p class="text-slate-600 mt-1">Buy tickets securely, receive your QR code instantly, and just show up and enjoy.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- For Vendors --}}
                <div class="surface p-8 sm:p-10 border-emerald-200 bg-emerald-50/30">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 bg-emerald-700 text-white rounded flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900">For Organizers</h3>
                    </div>
                    <div class="space-y-8">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded border border-emerald-200 bg-white flex items-center justify-center text-emerald-700 shrink-0 shadow-sm font-bold">1</div>
                            <div>
                                <p class="font-bold text-slate-900 text-lg">Create Your Profile</p>
                                <p class="text-slate-600 mt-1">Set up your vendor page with gallery, opening hours, map location, and social links.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded border border-emerald-200 bg-white flex items-center justify-center text-emerald-700 shrink-0 shadow-sm font-bold">2</div>
                            <div>
                                <p class="font-bold text-slate-900 text-lg">Post Events</p>
                                <p class="text-slate-600 mt-1">Create rich event listings with media, date &amp; time, audience tags, and ticket types.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded border border-emerald-200 bg-white flex items-center justify-center text-emerald-700 shrink-0 shadow-sm font-bold">3</div>
                            <div>
                                <p class="font-bold text-slate-900 text-lg">Manage Tickets</p>
                                <p class="text-slate-600 mt-1">Track orders, manage ticket inventory, and validate attendees with QR code check-in.</p>
                            </div>
                        </div>
                    </div>
                    @guest
                        <a href="{{ route('register') }}" class="btn-emerald mt-8 w-full">Join as an Organizer</a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- Trust Features & Newsletter --}}
    <section class="section bg-slate-900 text-white">
        <div class="app-content">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 text-left border-b border-slate-700 pb-16">
                <div>
                    <div class="w-12 h-12 bg-slate-800 rounded flex items-center justify-center border border-slate-700 text-emerald-400 mb-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Verified Organisers</h3>
                    <p class="text-slate-400 leading-relaxed text-sm">Trust who you book with. Profiles include organiser details, event history, and real follower counts.</p>
                </div>
                <div>
                    <div class="w-12 h-12 bg-slate-800 rounded flex items-center justify-center border border-slate-700 text-emerald-400 mb-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Secure Ticketing</h3>
                    <p class="text-slate-400 leading-relaxed text-sm">Stripe-powered checkout, instant ticket confirmation, and smooth QR-based event entry.</p>
                </div>
                <div>
                    <div class="w-12 h-12 bg-slate-800 rounded flex items-center justify-center border border-slate-700 text-emerald-400 mb-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Location-Aware</h3>
                    <p class="text-slate-400 leading-relaxed text-sm">Search events near your current location or plan ahead for any city in the world.</p>
                </div>
            </div>

            <div class="pt-16 max-w-3xl">
                <h3 class="text-3xl font-bold">Never Miss an Event Again</h3>
                <p class="mt-4 text-slate-400 text-lg">Subscribe for weekly recommendations and exclusive offers.</p>
                <form class="mt-8 flex flex-col sm:flex-row gap-3">
                    <input type="email" placeholder="your@email.com" class="flex-1 rounded bg-slate-800 border-none px-5 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <button type="button" class="btn-emerald font-bold px-8">Subscribe</button>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
