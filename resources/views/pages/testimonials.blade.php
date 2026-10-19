<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">{{ __('Testimonials') }}</h2>
            <p class="page-subtitle">{{ __('What organizers and attendees say about using Huddle for high-quality event experiences.') }}</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <div class="card p-7">
                <p class="text-sm uppercase tracking-wide text-emerald-700 font-semibold">{{ __('Organizer') }}</p>
                <p class="mt-4">"{{ __('Our registration flow became smoother immediately. We reduced check-in time by more than 40% in the first month.') }}"</p>
                <p class="mt-6 text-sm font-semibold text-slate-900">Nadia Williams, {{ __('Event Director') }}</p>
            </div>
            <div class="card p-7">
                <p class="text-sm uppercase tracking-wide text-emerald-700 font-semibold">{{ __('Organizer') }}</p>
                <p class="mt-4">"{{ __('The dashboard gives us exactly what we need: ticket trends, audience segments, and campaign performance in one place.') }}"</p>
                <p class="mt-6 text-sm font-semibold text-slate-900">Rehan Malik, {{ __('Growth Lead') }}</p>
            </div>
            <div class="card p-7">
                <p class="text-sm uppercase tracking-wide text-emerald-700 font-semibold">{{ __('Attendee') }}</p>
                <p class="mt-4">"{{ __('Discovering relevant local events is much easier now. The experience from search to checkout is very polished.') }}"</p>
                <p class="mt-6 text-sm font-semibold text-slate-900">Paula Romero, {{ __('Community Member') }}</p>
            </div>
            <div class="card p-7">
                <p class="text-sm uppercase tracking-wide text-emerald-700 font-semibold">{{ __('Operations') }}</p>
                <p class="mt-4">"{{ __('Support is fast and practical. Every issue we raised got a clear resolution and a realistic timeline.') }}"</p>
                <p class="mt-6 text-sm font-semibold text-slate-900">Brent Cooper, {{ __('Venue Manager') }}</p>
            </div>
            <div class="card p-7">
                <p class="text-sm uppercase tracking-wide text-emerald-700 font-semibold">{{ __('Enterprise') }}</p>
                <p class="mt-4">"{{ __('Their team handled onboarding for multiple departments without disrupting our existing event schedule.') }}"</p>
                <p class="mt-6 text-sm font-semibold text-slate-900">Amina Farooq, {{ __('Program Manager') }}</p>
            </div>
            <div class="card p-7">
                <p class="text-sm uppercase tracking-wide text-emerald-700 font-semibold">{{ __('Community') }}</p>
                <p class="mt-4">"{{ __('The platform helps us keep everything consistent, from branding to attendee communication after the event.') }}"</p>
                <p class="mt-6 text-sm font-semibold text-slate-900">Daniel Scott, {{ __('Community Organizer') }}</p>
            </div>
        </div>
    </section>
</x-app-layout>
