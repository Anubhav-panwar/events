<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Search Events</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('search') }}" class="card p-4 mb-4 grid grid-cols-1 md:grid-cols-5 gap-2">
                <input class="border rounded p-2" type="text" name="q" placeholder="Keyword" value="{{ request('q') }}">
                <input class="border rounded p-2" type="text" name="city" placeholder="City/place" value="{{ request('city') }}">
                <input class="border rounded p-2" type="date" name="start_date" value="{{ request('start_date') }}">
                <input class="border rounded p-2" type="date" name="end_date" value="{{ request('end_date') }}">
                <button class="btn-primary">Search</button>
                <div class="md:col-span-5 grid grid-cols-1 md:grid-cols-3 gap-2 mt-2">
                    <input class="border rounded p-2" type="number" step="0.000001" name="lat" placeholder="Latitude" value="{{ request('lat') }}">
                    <input class="border rounded p-2" type="number" step="0.000001" name="lng" placeholder="Longitude" value="{{ request('lng') }}">
                    <input class="border rounded p-2" type="number" step="1" name="radius_km" placeholder="Radius (km)" value="{{ request('radius_km') }}">
                </div>
            </form>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($events as $event)
                    <div class="card p-4">
                        <a class="font-semibold text-blue-600" href="{{ route('events.show', $event->slug) }}">{{ $event->title }}</a>
                        <div class="text-sm text-gray-600">{{ $event->event_date->format('Y-m-d') }} • {{ $event->address }}</div>
                        @isset($event->distance)
                            <div class="text-xs text-gray-500">{{ number_format($event->distance, 2) }} km away</div>
                        @endisset
                        <div class="mt-2 text-sm">{{ \Illuminate\Support\Str::limit($event->description, 120) }}</div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $events->links() }}</div>
        </div>
    </div>
</x-app-layout>
