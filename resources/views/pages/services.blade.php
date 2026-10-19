<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">{{ __('Services') }}</h2>
            <p class="page-subtitle">{{ __('Everything event teams need to manage discovery, sales, and attendee experience in one place.') }}</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div class="card p-6">
                    <div class="badge mb-3">{{ __('Core') }}</div>
                    <h3 class="text-xl font-semibold">{{ __('Event Publishing') }}</h3>
                    <p class="mt-3 text-sm">{{ __('Create branded event pages with schedules, media, speaker highlights, and venue details.') }}</p>
                </div>
                <div class="card p-6">
                    <div class="badge mb-3">{{ __('Core') }}</div>
                    <h3 class="text-xl font-semibold">{{ __('Ticketing & Checkout') }}</h3>
                    <p class="mt-3 text-sm">{{ __('Flexible ticket tiers, promo codes, and secure checkout flows optimized for conversion.') }}</p>
                </div>
                <div class="card p-6">
                    <div class="badge mb-3">{{ __('Core') }}</div>
                    <h3 class="text-xl font-semibold">{{ __('Vendor Profiles') }}</h3>
                    <p class="mt-3 text-sm">{{ __('Showcase organizer credibility with portfolios, categories, and social proof.') }}</p>
                </div>
                <div class="card p-6">
                    <div class="badge mb-3">{{ __('Operations') }}</div>
                    <h3 class="text-xl font-semibold">{{ __('QR Check-In') }}</h3>
                    <p class="mt-3 text-sm">{{ __('Speed up entry lines with instant ticket verification and real-time attendance logs.') }}</p>
                </div>
                <div class="card p-6">
                    <div class="badge mb-3">{{ __('Operations') }}</div>
                    <h3 class="text-xl font-semibold">{{ __('Order Management') }}</h3>
                    <p class="mt-3 text-sm">{{ __('Track purchases, refunds, and attendee records from a single organizer dashboard.') }}</p>
                </div>
                <div class="card p-6">
                    <div class="badge mb-3">{{ __('Growth') }}</div>
                    <h3 class="text-xl font-semibold">{{ __('Discovery Engine') }}</h3>
                    <p class="mt-3 text-sm">{{ __('Increase reach through category and city filters, featured placements, and curated lists.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="app-content">
            <div class="surface p-8">
                <h3 class="text-2xl font-bold">{{ __('Need a tailored package?') }}</h3>
                <p class="mt-3 text-slate-600">{{ __('We also support enterprise onboarding, custom integrations, and account migration for large teams.') }}</p>
                <a href="{{ route('contact') }}" class="btn-primary mt-6">{{ __('Talk to Sales') }}</a>
            </div>
        </div>
    </section>
</x-app-layout>
