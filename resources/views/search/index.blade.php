<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    @endpush

    {{-- Hero Search Bar --}}
    <div class="bg-gradient-to-br from-emerald-700 via-teal-700 to-sky-700 pt-10 pb-14">
        <div class="app-content text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Explore Events</h1>
            <p class="text-emerald-100 mb-8 text-sm">Find events near you or search any destination around the world</p>

            <form method="GET" action="{{ route('search') }}" id="searchForm" class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-5 max-w-4xl mx-auto">
                {{-- Row 1: Main Search --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input class="input-field pl-10 text-sm" type="text" name="q" placeholder="What? (keyword, event name...)" value="{{ request('q') }}">
                    </div>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        <input class="input-field pl-10 text-sm" type="text" name="place" placeholder="Where? (city, destination...)" value="{{ request('place') }}">
                    </div>
                    <select class="input-field text-sm" name="category_id">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) request('category_id') === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Row 2: Filters --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                    <select class="input-field text-sm" name="event_type">
                        <option value="">Free + Paid</option>
                        <option value="free" @selected(request('event_type') === 'free')>Free Only</option>
                        <option value="paid" @selected(request('event_type') === 'paid')>Paid Only</option>
                    </select>
                    <input class="input-field text-sm" type="date" name="start_date" value="{{ request('start_date') }}" placeholder="From date">
                    <input class="input-field text-sm" type="date" name="end_date" value="{{ request('end_date') }}" placeholder="To date">
                    <select class="input-field text-sm" name="sort">
                        <option value="date_asc" @selected(request('sort', 'date_asc') === 'date_asc')>Soonest first</option>
                        <option value="date_desc" @selected(request('sort') === 'date_desc')>Latest first</option>
                        <option value="distance" @selected(request('sort') === 'distance')>Nearest</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low–High</option>
                        <option value="featured" @selected(request('sort') === 'featured')>Featured first</option>
                    </select>
                </div>

                {{-- Row 3: Location --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                    <input class="input-field text-sm" type="number" step="0.000001" name="lat" id="latField" placeholder="Latitude" value="{{ request('lat') }}">
                    <input class="input-field text-sm" type="number" step="0.000001" name="lng" id="lngField" placeholder="Longitude" value="{{ request('lng') }}">
                    <input class="input-field text-sm" type="number" name="radius_km" id="radiusField" placeholder="Radius (km)" value="{{ request('radius_km', 25) }}">
                </div>

                {{-- Location Shortcuts --}}
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <button type="button" id="useMyLocationBtn" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white text-emerald-700 font-semibold text-sm shadow hover:shadow-md transition-all hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        Use My Location
                    </button>
                    <span class="text-white/60 text-xs ml-1">Radius:</span>
                    @foreach([5,10,25,50,100] as $r)
                        <button type="button" class="radius-btn px-3 py-1.5 rounded-xl text-sm font-medium transition-all border {{ request('radius_km', 25) == $r ? 'bg-white text-emerald-700 border-white shadow' : 'bg-white/15 text-white border-white/30 hover:bg-white/25' }}" data-radius="{{ $r }}">
                            {{ $r }} km
                        </button>
                    @endforeach
                    @if($resolvedPlace)
                        <span class="text-emerald-200 text-xs">📍 {{ $resolvedPlace['display_name'] }}</span>
                    @endif
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn-primary flex-1 sm:flex-none sm:px-10 text-sm">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Search Events
                    </button>
                    <a href="{{ route('search') }}" class="btn-secondary text-sm px-5">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <section class="page-section bg-slate-50">
        <div class="app-content space-y-6">

            {{-- Map (collapsible) --}}
            <div x-data="{ showMap: {{ request()->hasAny(['lat','lng','place','city']) ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm text-slate-600">
                        <span class="font-semibold text-slate-900">{{ $events->total() }}</span> events found
                        @if(request('q'))
                            for "<span class="italic">{{ request('q') }}</span>"
                        @endif
                    </div>
                    <button @click="showMap = !showMap" class="btn-secondary text-xs px-4 py-2">
                        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <span x-text="showMap ? 'Hide Map' : 'Show Map'">Show Map</span>
                    </button>
                </div>

                <div x-show="showMap" x-transition class="surface p-3 mb-6">
                    <div id="eventsMap" class="h-72 md:h-96 rounded-xl"></div>
                </div>
            </div>

            {{-- Category Filter Tabs --}}
            @if($categories->count())
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('search', array_merge(request()->except('category_id'), [])) }}"
                       class="px-4 py-2 rounded-xl text-sm font-medium transition-all {{ !request('category_id') ? 'bg-emerald-600 text-white shadow-md' : 'bg-white border border-slate-200 text-slate-700 hover:border-emerald-300 hover:text-emerald-700' }}">
                        All
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('search', array_merge(request()->except('category_id'), ['category_id' => $cat->id])) }}"
                           class="px-4 py-2 rounded-xl text-sm font-medium transition-all {{ (int)request('category_id') === $cat->id ? 'bg-emerald-600 text-white shadow-md' : 'bg-white border border-slate-200 text-slate-700 hover:border-emerald-300 hover:text-emerald-700' }}">
                            {{ $cat->icon ?? '' }} {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Results --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($events as $event)
                    <x-event-card :event="$event" />
                @empty
                    <div class="md:col-span-3 surface p-12 text-center">
                        <div class="text-5xl mb-4">🔍</div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">No events found</h3>
                        <p class="text-slate-600 mb-5">Try adjusting your search filters or expanding the search area.</p>
                        <a href="{{ route('search') }}" class="btn-primary">Clear Filters</a>
                    </div>
                @endforelse
            </div>

            <div>{{ $events->links() }}</div>
        </div>
    </section>

    @php
        $mapPoints = $events->getCollection()->map(function ($e) {
            return [
                'title' => $e->title,
                'lat'   => $e->latitude,
                'lng'   => $e->longitude,
                'url'   => route('events.show', $e->slug),
                'date'  => optional($e->event_date)->format('M j, Y'),
                'type'  => $e->event_type,
            ];
        })->filter(function ($e) {
            return !is_null($e['lat']) && !is_null($e['lng']);
        })->values();
    @endphp
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        const latField = document.getElementById('latField');
        const lngField = document.getElementById('lngField');
        const radiusField = document.getElementById('radiusField');

        document.getElementById('useMyLocationBtn')?.addEventListener('click', function () {
            if (!navigator.geolocation) return alert('Geolocation is not supported by your browser.');
            this.textContent = '📍 Locating...';
            navigator.geolocation.getCurrentPosition(function (position) {
                latField.value = position.coords.latitude.toFixed(6);
                lngField.value = position.coords.longitude.toFixed(6);
                if (!radiusField.value) radiusField.value = 25;
                document.getElementById('searchForm').submit();
            }, function () {
                alert('Unable to get your location. Please allow location access.');
            });
        });

        document.querySelectorAll('.radius-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                radiusField.value = btn.dataset.radius;
                document.querySelectorAll('.radius-btn').forEach(function (b) {
                    b.classList.remove('bg-white','text-emerald-700','border-white','shadow');
                    b.classList.add('bg-white/15','text-white','border-white/30');
                });
                btn.classList.add('bg-white','text-emerald-700','border-white','shadow');
                btn.classList.remove('bg-white/15','text-white','border-white/30');
            });
        });

        const mapEl = document.getElementById('eventsMap');
        if (mapEl) {
            const map = L.map('eventsMap');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const points = @json($mapPoints);

            if (points.length) {
                const bounds = [];
                points.forEach(function (p) {
                    const label = p.type === 'free' ? 'FREE' : 'PAID';
                    const icon = L.divIcon({
                        html: '<div style="background:linear-gradient(135deg,#059669,#0284c7);color:white;padding:4px 8px;border-radius:8px;font-size:11px;font-weight:600;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,0.3)">' + label + '</div>',
                        className: '',
                        iconAnchor: [20, 20]
                    });
                    const m = L.marker([p.lat, p.lng], { icon }).addTo(map);
                    m.bindPopup('<a href="' + p.url + '" style="font-weight:700;color:#059669">' + p.title + '</a><br><small>' + (p.date || '') + '</small>');
                    bounds.push([p.lat, p.lng]);
                });
                map.fitBounds(bounds, { padding: [30, 30] });
            } else {
                map.setView([20, 0], 2);
            }
        }
    </script>
    @endpush
</x-app-layout>
