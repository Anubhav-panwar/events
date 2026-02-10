<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Explore Events</h2>
            <p class="page-subtitle">Find events by keyword, date, location, and distance with a unified search experience.</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content space-y-6">
            <form method="GET" action="{{ route('search') }}" class="surface p-5 grid grid-cols-1 md:grid-cols-5 gap-3">
                <input class="input-field" type="text" name="q" placeholder="Keyword" value="{{ request('q') }}">
                <input class="input-field" type="text" name="city" placeholder="City / place" value="{{ request('city') }}">
                <input class="input-field" type="date" name="start_date" value="{{ request('start_date') }}">
                <input class="input-field" type="date" name="end_date" value="{{ request('end_date') }}">
                <button class="btn-primary">Search</button>
                <div class="md:col-span-5 grid grid-cols-1 md:grid-cols-3 gap-3 mt-1">
                    <input class="input-field" type="number" step="0.000001" name="lat" placeholder="Latitude" value="{{ request('lat') }}">
                    <input class="input-field" type="number" step="0.000001" name="lng" placeholder="Longitude" value="{{ request('lng') }}">
                    <input class="input-field" type="number" step="1" name="radius_km" placeholder="Radius (km)" value="{{ request('radius_km') }}">
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @forelse($events as $event)
                    <div class="surface p-5">
                        <a class="font-semibold text-blue-700 hover:text-blue-800" href="{{ route('events.show', $event->slug) }}">{{ $event->title }}</a>
                        <div class="text-sm text-slate-600 mt-2">{{ $event->event_date->format('Y-m-d') }} • {{ $event->address }}</div>
                        @isset($event->distance)
                            <div class="text-xs text-slate-500 mt-1">{{ number_format($event->distance, 2) }} km away</div>
                        @endisset
                        <div class="mt-3 text-sm text-slate-700">{{ \Illuminate\Support\Str::limit($event->description, 120) }}</div>
                    </div>
                @empty
                    <div class="md:col-span-3 surface p-8 text-center text-slate-600">No events found for your current filters.</div>
                @endforelse
            </div>

            <div>{{ $events->links() }}</div>
        </div>
    </section>
</x-app-layout>
