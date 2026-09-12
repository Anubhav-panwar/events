<x-app-layout>
    @push('meta')
        <title>About Huddle — Connecting Communities Through Memorable Live Events</title>
        <meta name="description" content="Learn about Huddle's mission to make discovering, booking, and hosting live experiences simple, secure, and delightful for everyone.">
    @endpush

    {{-- Hero Section --}}
    <section class="relative bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 text-white py-20 md:py-28 overflow-hidden border-b border-slate-800">
        <div class="absolute -right-20 -top-20 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="app-content relative z-10 max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-300 text-xs font-bold uppercase tracking-wider mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                The Huddle Story &amp; Vision
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight text-white leading-tight">
                Uniting Communities Through <br class="hidden sm:inline"/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-emerald-200">Real-World Experiences.</span>
            </h1>
            <p class="mt-6 text-lg sm:text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed">
                We believe the most memorable moments in life happen in person. Huddle bridges the gap between passionate event organizers and attendees seeking extraordinary live gatherings.
            </p>
            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('events.index') }}" class="btn-emerald px-7 py-3 text-base font-bold shadow-lg shadow-emerald-950/30">
                    Explore Platform Events
                </a>
                <a href="{{ route('vendors.index') }}" class="btn-secondary px-7 py-3 text-base font-bold bg-slate-800 text-white border-slate-700 hover:bg-slate-700">
                    Meet Our Organizers &rarr;
                </a>
            </div>
        </div>
    </section>

    {{-- Platform Impact Numbers Strip --}}
    <section class="bg-white border-b border-slate-200 py-12">
        <div class="app-content">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-slate-200">
                <div class="pt-4 md:pt-0">
                    <p class="text-4xl sm:text-5xl font-bold text-slate-900 tracking-tight">15,000+</p>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mt-1.5">Tickets Issued</p>
                    <p class="text-xs text-slate-400 mt-0.5">Seamless QR check-ins</p>
                </div>
                <div class="pt-4 md:pt-0">
                    <p class="text-4xl sm:text-5xl font-bold text-emerald-700 tracking-tight">500+</p>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mt-1.5">Verified Organizers</p>
                    <p class="text-xs text-slate-400 mt-0.5">Concerts, tech &amp; sports</p>
                </div>
                <div class="pt-4 md:pt-0">
                    <p class="text-4xl sm:text-5xl font-bold text-slate-900 tracking-tight">45+</p>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mt-1.5">Cities Covered</p>
                    <p class="text-xs text-slate-400 mt-0.5">Across 12 countries</p>
                </div>
                <div class="pt-4 md:pt-0">
                    <p class="text-4xl sm:text-5xl font-bold text-emerald-700 tracking-tight">99.9%</p>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mt-1.5">Admission Success</p>
                    <p class="text-xs text-slate-400 mt-0.5">Zero counterfeit tickets</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Mission & Origin Section --}}
    <section class="py-16 md:py-24 bg-slate-50 border-b border-slate-200">
        <div class="app-content">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-6 space-y-6">
                    <span class="brand-pill">Why We Built Huddle</span>
                    <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-slate-900 leading-tight">
                        Ending the Era of Clunky, Fragmented Event Ticketing.
                    </h2>
                    <p class="text-slate-700 leading-relaxed text-base">
                        Until now, organizers had to juggle disjointed tools: one platform for email signups, another for expensive ticket processing, and a third for door admissions. Meanwhile, attendees faced hidden fees, scam scalpers, and confusing apps.
                    </p>
                    <p class="text-slate-700 leading-relaxed text-base">
                        Huddle was founded on a simple principle: <strong>make live event discovery thrilling and ticketing completely effortless</strong>. Whether you are hosting an intimate 20-person culinary workshop or a 5,000-attendee music festival, our platform provides a turnkey CRM, transparent pricing, and instant QR verification.
                    </p>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm mb-2">0%</div>
                            <h4 class="text-sm font-bold text-slate-900">Zero Fees on Free Events</h4>
                            <p class="text-xs text-slate-500 mt-1">Community meetups should be completely free to organize.</p>
                        </div>
                        <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm mb-2">⚡</div>
                            <h4 class="text-sm font-bold text-slate-900">Instant Stripe Payouts</h4>
                            <p class="text-xs text-slate-500 mt-1">Direct card payments processed securely and deposited rapidly.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div class="rounded-2xl overflow-hidden shadow-lg aspect-[4/5] relative">
                                <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=800&q=80" alt="Concert Stage" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 to-transparent flex items-end p-4">
                                    <span class="text-white text-xs font-bold">Concerts &amp; Festivals</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4 pt-8">
                            <div class="rounded-2xl overflow-hidden shadow-lg aspect-[4/5] relative">
                                <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=800&q=80" alt="Tech Conference" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 to-transparent flex items-end p-4">
                                    <span class="text-white text-xs font-bold">Tech &amp; Innovation Summits</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Platform Core Pillars --}}
    <section class="py-16 md:py-24 bg-white border-b border-slate-200">
        <div class="app-content">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="brand-pill">Our Core Pillars</span>
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-slate-900 mt-3">
                    Built on Trust, Velocity &amp; Community
                </h2>
                <p class="mt-3 text-slate-600 text-base">
                    Every feature we ship is designed to elevate the attendee experience and empower event creators.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                {{-- Pillar 1 --}}
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200/90 shadow-sm hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">1. Verified Authenticity</h3>
                    <p class="text-slate-600 text-sm mt-3 leading-relaxed">
                        Every organizer profile undergoes administrative moderation. Attendees always know exactly who is hosting the event, complete with past reviews and official business verification.
                    </p>
                </div>

                {{-- Pillar 2 --}}
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200/90 shadow-sm hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">2. Bank-Grade Security</h3>
                    <p class="text-slate-600 text-sm mt-3 leading-relaxed">
                        Backed by official Stripe infrastructure with 256-bit SSL encryption. We support all major credit cards, Apple Pay, and Google Pay with zero stored sensitive card data.
                    </p>
                </div>

                {{-- Pillar 3 --}}
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200/90 shadow-sm hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">3. Frictionless Admission</h3>
                    <p class="text-slate-600 text-sm mt-3 leading-relaxed">
                        Unique encrypted QR tickets are generated instantaneously upon booking. Organizers scan attendees in 0.5 seconds via any mobile camera, eliminating queues at the gate.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Frequently Asked Questions (FAQ) --}}
    <section class="py-16 md:py-24 bg-slate-50 border-b border-slate-200">
        <div class="app-content max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <span class="brand-pill">Help &amp; Clarifications</span>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 mt-2">Frequently Asked Questions</h2>
                <p class="text-slate-600 text-sm mt-2">Everything you need to know about booking or hosting on Huddle.</p>
            </div>

            <div class="space-y-4">
                <div class="p-6 bg-white rounded-xl border border-slate-200 shadow-sm">
                    <h4 class="text-base font-bold text-slate-900 flex items-center justify-between">
                        <span>How do I receive my event ticket?</span>
                        <span class="text-emerald-600 text-lg">&bull;</span>
                    </h4>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Immediately upon completing registration or Stripe checkout, your ticket with its encrypted QR code is displayed on screen, sent to your registered email, and saved inside your account under "My Tickets".
                    </p>
                </div>

                <div class="p-6 bg-white rounded-xl border border-slate-200 shadow-sm">
                    <h4 class="text-base font-bold text-slate-900 flex items-center justify-between">
                        <span>What payment methods do you support?</span>
                        <span class="text-emerald-600 text-lg">&bull;</span>
                    </h4>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        We process payments via Stripe, supporting Visa, Mastercard, American Express, Discover, Apple Pay, and Google Pay across all international currencies configured by the organizer.
                    </p>
                </div>

                <div class="p-6 bg-white rounded-xl border border-slate-200 shadow-sm">
                    <h4 class="text-base font-bold text-slate-900 flex items-center justify-between">
                        <span>How can I list my own events as an organizer?</span>
                        <span class="text-emerald-600 text-lg">&bull;</span>
                    </h4>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Simply sign up for a free Huddle account, head to "Vendor Profile", add your organization details and logo, and you can immediately publish events, configure ticket tiers, and access your Organizer CRM.
                    </p>
                </div>

                <div class="p-6 bg-white rounded-xl border border-slate-200 shadow-sm">
                    <h4 class="text-base font-bold text-slate-900 flex items-center justify-between">
                        <span>Can I sync events to my Google or Apple calendar?</span>
                        <span class="text-emerald-600 text-lg">&bull;</span>
                    </h4>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Yes! Every event page features one-click "Add to Google Calendar" and a universal ".ICS Download" compatible with iOS, Outlook, and Android calendar applications.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Bottom Call to Action --}}
    <section class="py-20 bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 text-white text-center">
        <div class="app-content max-w-3xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-white leading-tight">
                Ready to Experience Something Incredible?
            </h2>
            <p class="mt-4 text-slate-300 text-base leading-relaxed">
                Discover your next memorable event or bring your community together with Huddle's all-in-one ticketing platform.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('events.index') }}" class="btn-emerald px-8 py-3.5 text-base font-bold shadow-lg shadow-emerald-950/40">
                    Find Events Near You
                </a>
                <a href="{{ route('register') }}" class="btn-secondary px-8 py-3.5 text-base font-bold bg-white text-slate-900 hover:bg-slate-100">
                    Host an Event
                </a>
            </div>
        </div>
    </section>
</x-app-layout>
