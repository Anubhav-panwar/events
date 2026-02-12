<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Pricing</h2>
            <p class="page-subtitle">Simple plans for growing teams, from first launch to high-volume event operations.</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="card p-8">
                    <h3 class="text-2xl font-bold">Starter</h3>
                    <p class="mt-2 text-sm text-slate-600">For small teams and community events.</p>
                    <div class="mt-6 text-4xl font-bold">$29<span class="text-base font-medium text-slate-500">/month</span></div>
                    <ul class="mt-6 space-y-2 text-sm text-slate-700">
                        <li>Up to 5 active events</li>
                        <li>Standard ticketing checkout</li>
                        <li>Email support</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="btn-secondary mt-6">Choose Starter</a>
                </div>

                <div class="card p-8 border-emerald-300 shadow-[0_22px_70px_-35px_rgba(16,185,129,0.5)]">
                    <div class="badge-gradient mb-3">Most Popular</div>
                    <h3 class="text-2xl font-bold">Professional</h3>
                    <p class="mt-2 text-sm text-slate-600">For scaling organizers with multiple venues.</p>
                    <div class="mt-6 text-4xl font-bold">$89<span class="text-base font-medium text-slate-500">/month</span></div>
                    <ul class="mt-6 space-y-2 text-sm text-slate-700">
                        <li>Up to 30 active events</li>
                        <li>Advanced analytics dashboard</li>
                        <li>Priority support and onboarding</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="btn-primary mt-6">Choose Professional</a>
                </div>

                <div class="card p-8">
                    <h3 class="text-2xl font-bold">Enterprise</h3>
                    <p class="mt-2 text-sm text-slate-600">For high-volume operations and custom workflows.</p>
                    <div class="mt-6 text-4xl font-bold">Custom</div>
                    <ul class="mt-6 space-y-2 text-sm text-slate-700">
                        <li>Unlimited active events</li>
                        <li>Custom integrations and SSO</li>
                        <li>Dedicated success manager</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="btn-secondary mt-6">Talk to Sales</a>
                </div>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="app-content">
            <div class="surface p-8">
                <h3 class="text-2xl font-bold">All plans include</h3>
                <p class="mt-2 text-slate-600">Secure payments, attendee communication tools, real-time order tracking, and access to platform updates.</p>
            </div>
        </div>
    </section>
</x-app-layout>
