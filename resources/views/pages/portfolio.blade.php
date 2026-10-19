<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">{{ __('Portfolio') }}</h2>
            <p class="page-subtitle">{{ __('A sample of event formats and campaign styles our clients run with Huddle.') }}</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <article class="card p-6">
                <div class="badge-gradient mb-3">{{ __('Conference') }}</div>
                <h3 class="text-xl font-semibold">{{ __('FutureTech Summit') }}</h3>
                <p class="mt-3 text-sm">{{ __('3-day conference with 2,500 attendees, multi-track schedules, and sponsor lead capture.') }}</p>
            </article>
            <article class="card p-6">
                <div class="badge-gradient mb-3">{{ __('Workshop') }}</div>
                <h3 class="text-xl font-semibold">{{ __('Creative Bootcamp') }}</h3>
                <p class="mt-3 text-sm">{{ __('Weekly design workshops sold out in under 48 hours using targeted city discovery pages.') }}</p>
            </article>
            <article class="card p-6">
                <div class="badge-gradient mb-3">{{ __('Festival') }}</div>
                <h3 class="text-xl font-semibold">{{ __('Urban Music Weekend') }}</h3>
                <p class="mt-3 text-sm">{{ __('Large-format event with fast QR gate entry and mobile-first ticketing for younger audiences.') }}</p>
            </article>
            <article class="card p-6">
                <div class="badge-gradient mb-3">{{ __('Corporate') }}</div>
                <h3 class="text-xl font-semibold">{{ __('Leadership Forum') }}</h3>
                <p class="mt-3 text-sm">{{ __('Closed registration, invite-only access, and role-based check-in for executive programming.') }}</p>
            </article>
            <article class="card p-6">
                <div class="badge-gradient mb-3">{{ __('Community') }}</div>
                <h3 class="text-xl font-semibold">{{ __('Neighborhood Makers Fair') }}</h3>
                <p class="mt-3 text-sm">{{ __('Local vendors coordinated through shared profiles, volunteer slots, and segmented announcements.') }}</p>
            </article>
            <article class="card p-6">
                <div class="badge-gradient mb-3">{{ __('Hybrid') }}</div>
                <h3 class="text-xl font-semibold">{{ __('Global Startup Day') }}</h3>
                <p class="mt-3 text-sm">{{ __('In-person and virtual attendance with unified ticket inventory and post-event conversion tracking.') }}</p>
            </article>
        </div>
    </section>
</x-app-layout>
