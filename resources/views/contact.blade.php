<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">{{ __('Contact Us') }}</h2>
            <p class="page-subtitle">{{ __('Tell us what you are planning and our team will get back within one business day.') }}</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content">
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="lg:col-span-2 surface p-8">
                    <div class="badge-gradient mb-4">{{ __('Start a Conversation') }}</div>
                    <h3 class="text-2xl font-bold">{{ __('How can we help?') }}</h3>
                    <p class="mt-2 text-slate-600">
                        {{ __('Whether you need platform guidance, enterprise planning, or onboarding support, share your details and our specialists will respond quickly.') }}
                    </p>
                    <form class="mt-8 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700" for="name">{{ __('Full name') }}</label>
                            <input id="name" type="text" class="input-field mt-2" placeholder="{{ __('Enter your name') }}">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700" for="email">{{ __('Work email') }}</label>
                            <input id="email" type="email" class="input-field mt-2" placeholder="{{ __('you@company.com') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-slate-700" for="subject">{{ __('Subject') }}</label>
                            <input id="subject" type="text" class="input-field mt-2" placeholder="{{ __('What do you need help with?') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-slate-700" for="message">{{ __('Message') }}</label>
                            <textarea id="message" rows="5" class="input-field mt-2" placeholder="{{ __('Share your goals, timeline, and event type...') }}"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <button type="button" class="btn-primary">{{ __('Send Message') }}</button>
                        </div>
                    </form>
                </div>
                <div class="space-y-6">
                    <div class="surface p-6">
                        <h4 class="text-lg font-semibold">{{ __('Contact Details') }}</h4>
                        <div class="mt-4 space-y-3 text-slate-700">
                            <p><span class="font-semibold">{{ __('Phone:') }}</span> +1 (555) 240-8891</p>
                            <p><span class="font-semibold">{{ __('Email:') }}</span> support@huddle.local</p>
                            <p><span class="font-semibold">{{ __('Office:') }}</span> 228 Market Street, San Francisco, CA</p>
                        </div>
                    </div>
                    <div class="surface p-6">
                        <h4 class="text-lg font-semibold">{{ __('Working Hours') }}</h4>
                        <ul class="mt-4 space-y-2 text-slate-700 text-sm">
                            <li>{{ __('Monday - Friday: 9:00 AM to 7:00 PM') }}</li>
                            <li>{{ __('Saturday: 10:00 AM to 4:00 PM') }}</li>
                            <li>{{ __('Sunday: Emergency support only') }}</li>
                        </ul>
                    </div>
                    <div class="surface p-6">
                        <h4 class="text-lg font-semibold">{{ __('Need quick answers?') }}</h4>
                        <p class="mt-2 text-sm text-slate-600">{{ __('Visit our FAQ for booking, payment, and organizer setup details.') }}</p>
                        <a href="{{ route('faq') }}" class="btn-secondary mt-4">{{ __('Open FAQ') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
