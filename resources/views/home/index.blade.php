<x-app-layout>
    <section class="relative overflow-hidden pt-10 pb-16 md:pt-16 md:pb-20">
        <div class="absolute inset-0 bg-gradient-to-b from-emerald-50/80 to-transparent"></div>
        <div class="relative app-content">
            <div class="grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <span class="badge-gradient">Trusted Event Platform</span>
                    <h1 class="mt-5 text-4xl font-bold leading-tight text-slate-900 md:text-6xl">
                        Discover Events.
                        <span class="gradient-text block">Book Faster. Enjoy More.</span>
                    </h1>
                    <p class="mt-5 max-w-xl text-base text-slate-600 md:text-lg">
                        Huddle helps people find high-quality events and gives organizers reliable tools for ticketing, check-in, and growth.
                    </p>
                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('search') }}" class="btn-primary px-8 py-4 text-base">Explore Events</a>
                        <a href="{{ route('contact') }}" class="btn-secondary px-8 py-4 text-base">Talk to Team</a>
                    </div>
                    <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="surface p-4 text-center">
                            <p class="text-2xl font-bold text-slate-900">{{ number_format($stats['events']) }}+</p>
                            <p class="text-sm text-slate-500">Events</p>
                        </div>
                        <div class="surface p-4 text-center">
                            <p class="text-2xl font-bold text-slate-900">{{ number_format($stats['vendors']) }}+</p>
                            <p class="text-sm text-slate-500">Vendors</p>
                        </div>
                        <div class="surface p-4 text-center">
                            <p class="text-2xl font-bold text-slate-900">{{ number_format($stats['cities']) }}+</p>
                            <p class="text-sm text-slate-500">Cities</p>
                        </div>
                        <div class="surface p-4 text-center">
                            <p class="text-2xl font-bold text-slate-900">4.9</p>
                            <p class="text-sm text-slate-500">Rating</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="media-tile aspect-[16/10]">
                        <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1200&q=80" alt="Event audience">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="media-tile aspect-[4/3]">
                            <img src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=800&q=80" alt="Networking event">
                        </div>
                        <div class="media-tile aspect-[4/3]">
                            <img src="https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&fit=crop&w=800&q=80" alt="Graduation event">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-center text-sm font-semibold text-slate-500">Used by trusted organizers and communities</p>
                <div class="mt-4 grid grid-cols-2 gap-3 text-center sm:grid-cols-3 lg:grid-cols-6">
                    <div class="brand-pill">NovaStage</div>
                    <div class="brand-pill">EventLoop</div>
                    <div class="brand-pill">CityCulture</div>
                    <div class="brand-pill">BrightMeet</div>
                    <div class="brand-pill">PulseFest</div>
                    <div class="brand-pill">GatherPro</div>
                </div>
            </div>
        </div>
    </section>

    <section class="section bg-white">
        <div class="app-content">
            <div class="text-center mb-10">
                <h2 class="section-header">Trending Events</h2>
                <p class="section-subtitle">Popular picks this week</p>
            </div>
            <div class="flex items-center justify-center gap-3 mb-8 flex-wrap">
                <a href="{{ route('search') }}" class="btn-primary px-5 py-2 text-sm">All Events</a>
                @foreach($categories as $cat)
                    <a href="{{ route('search', ['category_id' => $cat->id]) }}" class="px-4 py-2 rounded-full bg-white border border-slate-200 text-slate-700 text-sm font-medium hover:border-emerald-300 hover:text-emerald-700">{{ $cat->name }}</a>
                @endforeach
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($upcoming as $event)
                    <x-event-card :event="$event" />
                @empty
                    <div class="col-span-3 surface p-8 text-center text-slate-600">No upcoming events yet. Check back soon.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section">
        <div class="app-content grid gap-6 lg:grid-cols-3">
            <div class="card p-7">
                <h3 class="text-xl font-bold text-slate-900">Verified Organizers</h3>
                <p class="mt-3 text-slate-600">Trust who you book with. Profiles include organizer details and event history.</p>
            </div>
            <div class="card p-7">
                <h3 class="text-xl font-bold text-slate-900">Secure Ticketing</h3>
                <p class="mt-3 text-slate-600">Fast checkout, instant confirmation, and smooth QR-based event entry.</p>
            </div>
            <div class="card p-7">
                <h3 class="text-xl font-bold text-slate-900">Reliable Support</h3>
                <p class="mt-3 text-slate-600">Our team supports attendees and organizers from setup to event day.</p>
            </div>
        </div>
    </section>

    <section class="section bg-gradient-to-br from-emerald-600 via-teal-600 to-sky-600 text-white">
        <div class="app-content">
            <div class="text-center">
                <h2 class="text-3xl md:text-4xl font-bold">Never Miss an Event</h2>
                <p class="mt-3 text-lg opacity-90">Get weekly recommendations and exclusive offers.</p>
                <form class="mt-8 mx-auto flex max-w-xl flex-col gap-3 sm:flex-row">
                    <input type="email" placeholder="Enter your email" class="flex-1 rounded-xl px-5 py-3 text-slate-900 focus:outline-none focus:ring-4 focus:ring-white/30">
                    <button type="button" class="rounded-xl bg-white px-6 py-3 font-semibold text-emerald-700 hover:bg-emerald-50">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

    <section class="section bg-gray-900 text-gray-300">
        <div class="app-content">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div>
                    <h3 class="text-2xl font-bold text-white">Huddle</h3>
                    <p class="mt-3 text-gray-400">Modern event discovery and ticketing experience built for trust and speed.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-white">Quick Links</h4>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-white">About</a></li>
                        <li><a href="{{ route('services') }}" class="hover:text-white">Services</a></li>
                        <li><a href="{{ route('search') }}" class="hover:text-white">Browse Events</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white">Support</h4>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('faq') }}" class="hover:text-white">Help Center</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                        <li><a href="{{ route('testimonials') }}" class="hover:text-white">Testimonials</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 border-t border-gray-800 pt-6 text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} Huddle. All rights reserved.</p>
            </div>
        </div>
    </section>
</x-app-layout>
