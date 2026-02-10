<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Dashboard</h2>
            <p class="page-subtitle">Track your activity and jump back into planning in one place.</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content">
            <div class="surface p-8">
                <h3 class="text-lg font-semibold text-slate-900">Welcome back 👋</h3>
                <p class="mt-2 text-slate-600">You are logged in successfully. Use the navigation above to explore events, vendors, and your account tools.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('search') }}" class="btn-primary">Explore Events</a>
                    <a href="{{ route('vendors.index') }}" class="btn-secondary">Browse Vendors</a>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
