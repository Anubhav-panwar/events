<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">About Huddle</h2>
            <p class="page-subtitle">We help communities discover, host, and scale memorable events through one reliable platform.</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2 surface p-8">
                <div class="badge-gradient mb-4">Our Mission</div>
                <h3 class="text-3xl font-bold">Building better local experiences, one event at a time.</h3>
                <p class="mt-4 text-slate-700">
                    Huddle was created to solve a common challenge: great events were hard to discover, and organizing them was harder than it should be.
                    Our platform connects attendees, organizers, and vendors with practical tools for discovery, ticketing, and check-in.
                </p>
                <p class="mt-4 text-slate-700">
                    From neighborhood workshops to large conferences, we focus on fast setup, transparent pricing, and dependable attendee experiences.
                </p>
            </div>
            <div class="surface p-8">
                <h4 class="text-xl font-semibold">At a Glance</h4>
                <div class="mt-6 space-y-4">
                    <div>
                        <div class="text-3xl font-bold text-emerald-700">10,000+</div>
                        <p class="text-sm text-slate-600">Monthly active attendees</p>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-emerald-700">1,200+</div>
                        <p class="text-sm text-slate-600">Partner organizers</p>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-emerald-700">45+</div>
                        <p class="text-sm text-slate-600">Cities supported</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="app-content">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="card p-6">
                    <h4 class="text-xl font-semibold">Trust</h4>
                    <p class="mt-3 text-sm">Secure ticketing, reliable attendance data, and transparent communication for attendees and hosts.</p>
                </div>
                <div class="card p-6">
                    <h4 class="text-xl font-semibold">Speed</h4>
                    <p class="mt-3 text-sm">Launch event pages in minutes, automate reminders, and reduce admin overhead across your team.</p>
                </div>
                <div class="card p-6">
                    <h4 class="text-xl font-semibold">Growth</h4>
                    <p class="mt-3 text-sm">Reach the right audience with searchable listings, highlighted placements, and clear conversion insights.</p>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
