@props([
    'latitude' => null,
    'longitude' => null,
    'venueName' => null,
    'address' => null,
    'city' => null,
    'country' => null,
])

@php
    $lat = old('latitude', $latitude);
    $lng = old('longitude', $longitude);
    $apiKey = config('services.google.maps_api_key') ?: env('VITE_GOOGLE_MAPS_API_KEY');
    $pickerId = 'gmap_picker_' . uniqid();
@endphp

<div id="{{ $pickerId }}" class="space-y-3" x-data="googleMapPicker({
    initialLat: {{ $lat ? (float)$lat : 'null' }},
    initialLng: {{ $lng ? (float)$lng : 'null' }},
    apiKey: '{{ $apiKey }}'
})" x-init="init()">

    {{-- Hidden internal coordinate fields (vendors never need to type coordinates manually) --}}
    <input type="hidden" name="latitude" id="event_latitude" :value="coords.lat">
    <input type="hidden" name="longitude" id="event_longitude" :value="coords.lng">

    {{-- Top Action / Search Bar --}}
    <div class="space-y-2">
        <label class="block text-xs font-bold text-slate-700">
            {{ __('Pin Event Location on Map') }}
            <span class="text-[11px] font-normal text-slate-500 ml-1">({{ __('Search address or click on the map to set pin') }})</span>
        </label>

        <div class="flex flex-col sm:flex-row items-stretch gap-2">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input
                    type="text"
                    id="mapSearchInput"
                    x-ref="searchInput"
                    x-model="searchQuery"
                    @keydown.enter.prevent="searchLocation()"
                    placeholder="{{ __('Search venue, landmark, or street address on Google Maps...') }}"
                    class="input-field pr-8 text-sm"
                    style="padding-left: 2.5rem;"
                    autocomplete="off"
                >
                <button
                    type="button"
                    x-show="searchQuery"
                    @click="searchQuery = ''; $refs.searchInput.focus()"
                    class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600"
                    title="{{ __('Clear') }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="searchLocation()"
                    class="px-3.5 py-2 text-xs font-bold rounded-xl bg-slate-900 text-white hover:bg-slate-800 transition-all flex items-center justify-center gap-1.5 shadow-sm shrink-0"
                >
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>{{ __('Find on Map') }}</span>
                </button>

                <button
                    type="button"
                    @click="locateCurrentPosition()"
                    :disabled="isLocating"
                    class="px-3 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-all flex items-center justify-center gap-1 shadow-2xs shrink-0"
                    title="{{ __('Use My Current Location') }}"
                >
                    <svg class="w-3.5 h-3.5 text-emerald-600" :class="{'animate-spin': isLocating}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>{{ __('My Location') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Map Canvas Container --}}
    <div class="relative w-full h-80 rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 shadow-inner group">
        {{-- Map element --}}
        <div id="googleMapCanvas" x-ref="mapCanvas" class="w-full h-full"></div>

        {{-- Loading / Initializing Overlay --}}
        <div x-show="!mapReady" class="absolute inset-0 bg-slate-100/90 backdrop-blur-xs flex flex-col items-center justify-center gap-2 z-10 transition-opacity">
            <svg class="w-7 h-7 text-emerald-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            <span class="text-xs font-semibold text-slate-600">{{ __('Loading Google Maps...') }}</span>
        </div>

        {{-- Floating Tip overlay --}}
        <div class="absolute top-3 left-3 pointer-events-none z-10">
            <span class="px-2.5 py-1 bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-medium rounded-full shadow-md flex items-center gap-1.5 border border-white/20">
                <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                <span>{{ __('Click or drag pin to adjust exact location') }}</span>
            </span>
        </div>

        {{-- Fallback Notice if Leaflet fallback activated --}}
        <template x-if="usingFallback">
            <div class="absolute bottom-3 right-3 z-10">
                <span class="px-2 py-0.5 bg-amber-50 border border-amber-200 text-amber-800 text-[10px] font-bold rounded shadow-xs">
                    {{ __('OpenStreetMap / Leaflet Mode') }}
                </span>
            </div>
        </template>
    </div>

    {{-- Coordinate Status Badge & Reverse Geocode Info --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1 text-xs">
        <div class="flex items-center gap-2">
            <template x-if="hasCoords">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ __('Location Pinned') }}:</span>
                    <span class="font-mono text-[11px]" x-text="coords.lat.toFixed(5) + ', ' + coords.lng.toFixed(5)"></span>
                </span>
            </template>
            <template x-if="!hasCoords">
                <span class="inline-flex items-center gap-1.5 text-slate-500 font-medium">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ __('No pin set yet. Search an address or click on the map.') }}</span>
                </span>
            </template>
        </div>

        <template x-if="hasCoords">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="clearPin()"
                    class="text-xs text-slate-500 hover:text-red-600 transition-colors"
                >
                    {{ __('Clear Pin') }}
                </button>
                <span class="text-slate-300">|</span>
                <span class="text-slate-400 text-[11px]">{{ __('Coordinates saved internally') }}</span>
            </div>
        </template>
    </div>
</div>

<script>
function googleMapPicker(config) {
    return {
        apiKey: config.apiKey || '',
        coords: {
            lat: config.initialLat,
            lng: config.initialLng,
        },
        searchQuery: '',
        mapReady: false,
        isLocating: false,
        usingFallback: false,
        map: null,
        marker: null,
        geocoder: null,
        autocomplete: null,

        get hasCoords() {
            return this.coords.lat !== null && this.coords.lng !== null && !isNaN(this.coords.lat) && !isNaN(this.coords.lng);
        },

        init() {
            // Default center if no coordinates: Berlin or New York (40.7128, -74.0060)
            const defaultLat = this.hasCoords ? this.coords.lat : 40.7128;
            const defaultLng = this.hasCoords ? this.coords.lng : -74.0060;
            const defaultZoom = this.hasCoords ? 15 : 11;

            if (this.apiKey) {
                this.loadGoogleMapsApi()
                    .then(() => {
                        this.initGoogleMap(defaultLat, defaultLng, defaultZoom);
                    })
                    .catch((err) => {
                        console.warn('Google Maps API failed to load, falling back to Leaflet:', err);
                        this.initLeafletFallback(defaultLat, defaultLng, defaultZoom);
                    });
            } else {
                this.initLeafletFallback(defaultLat, defaultLng, defaultZoom);
            }
        },

        loadGoogleMapsApi() {
            return new Promise((resolve, reject) => {
                if (window.google && window.google.maps) {
                    return resolve();
                }

                const scriptId = 'google-maps-api-script';
                const existing = document.getElementById(scriptId);
                if (existing) {
                    existing.addEventListener('load', () => resolve());
                    existing.addEventListener('error', () => reject(new Error('Script load error')));
                    return;
                }

                // Global auth failure handler
                window.gm_authFailure = () => {
                    console.warn('Google Maps auth failure detected. Switching to fallback.');
                    this.initLeafletFallback(this.coords.lat || 40.7128, this.coords.lng || -74.0060, 12);
                };

                const script = document.createElement('script');
                script.id = scriptId;
                script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(this.apiKey)}&libraries=places`;
                script.async = true;
                script.defer = true;
                script.onload = () => resolve();
                script.onerror = (e) => reject(e);
                document.head.appendChild(script);
            });
        },

        initGoogleMap(lat, lng, zoom) {
            try {
                const mapOptions = {
                    center: { lat, lng },
                    zoom: zoom,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                    zoomControl: true,
                    styles: [
                        { featureType: 'poi', elementType: 'labels.icon', stylers: [{ visibility: 'off' }] },
                        { featureType: 'transit', stylers: [{ visibility: 'simplified' }] }
                    ]
                };

                this.map = new google.maps.Map(this.$refs.mapCanvas, mapOptions);
                this.geocoder = new google.maps.Geocoder();

                // If coordinates exist, add marker
                if (this.hasCoords) {
                    this.placeGoogleMarker(lat, lng, false);
                }

                // Map click listener to place/move marker
                this.map.addListener('click', (e) => {
                    const clickedLat = e.latLng.lat();
                    const clickedLng = e.latLng.lng();
                    this.setCoordinates(clickedLat, clickedLng);
                    this.placeGoogleMarker(clickedLat, clickedLng, true);
                    this.reverseGeocodeGoogle(clickedLat, clickedLng);
                });

                // Initialize Places Autocomplete on the search input
                if (window.google.maps.places && this.$refs.searchInput) {
                    this.autocomplete = new google.maps.places.Autocomplete(this.$refs.searchInput, {
                        types: ['geocode', 'establishment'],
                        fields: ['geometry', 'name', 'formatted_address', 'address_components']
                    });

                    this.autocomplete.addListener('place_changed', () => {
                        const place = this.autocomplete.getPlace();
                        if (!place || !place.geometry || !place.geometry.location) {
                            return;
                        }

                        const pLat = place.geometry.location.lat();
                        const pLng = place.geometry.location.lng();
                        this.setCoordinates(pLat, pLng);

                        this.map.setCenter({ lat: pLat, lng: pLng });
                        this.map.setZoom(16);
                        this.placeGoogleMarker(pLat, pLng, true);

                        // Populate form fields automatically
                        this.populateFormFieldsFromGooglePlace(place);
                    });
                }

                this.mapReady = true;
            } catch (e) {
                console.error('Error initializing Google Map:', e);
                this.initLeafletFallback(lat, lng, zoom);
            }
        },

        placeGoogleMarker(lat, lng, bounce = false) {
            if (this.marker) {
                this.marker.setPosition({ lat, lng });
            } else {
                this.marker = new google.maps.Marker({
                    position: { lat, lng },
                    map: this.map,
                    draggable: true,
                    title: 'Event Venue Location',
                    animation: bounce ? google.maps.Animation.DROP : null,
                });

                this.marker.addListener('dragend', () => {
                    const pos = this.marker.getPosition();
                    this.setCoordinates(pos.lat(), pos.lng());
                    this.reverseGeocodeGoogle(pos.lat(), pos.lng());
                });
            }
        },

        reverseGeocodeGoogle(lat, lng) {
            if (!this.geocoder) return;
            this.geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    this.populateFormFieldsFromGooglePlace(results[0], false);
                }
            });
        },

        reverseGeocodeFallback(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.ok ? res.json() : null)
            .then(place => {
                if (!place) return;
                const parts = place.address || {};
                const street = [parts.house_number, parts.road].filter(Boolean).join(' ');
                const city = parts.city || parts.town || parts.village || parts.suburb || parts.county || '';
                this.setLocationField('address', street || place.display_name || '');
                this.setLocationField('city', city);
                this.setLocationField('country', parts.country || '');
            })
            .catch(() => {});
        },

        setLocationField(name, value) {
            const input = document.querySelector(`input[name="${name}"]`);
            if (!input || !value) return;
            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        },

        populateFormFieldsFromGooglePlace(place, overrideVenue = true) {
            const components = place.address_components || [];
            let streetNumber = '';
            let route = '';
            let city = '';
            let country = '';

            for (const comp of components) {
                const types = comp.types || [];
                if (types.includes('street_number')) streetNumber = comp.long_name;
                if (types.includes('route')) route = comp.long_name;
                if (types.includes('locality') || types.includes('postal_town')) city = comp.long_name;
                if (!city && (types.includes('sublocality') || types.includes('administrative_area_level_2'))) city = comp.long_name;
                if (types.includes('country')) country = comp.long_name;
            }

            const streetAddress = (streetNumber ? streetNumber + ' ' : '') + route;

            // Fill input fields if found on page
            const venueInput = document.querySelector('input[name="venue_name"]');

            this.setLocationField('address', streetAddress || place.formatted_address || '');

            if (city) this.setLocationField('city', city);

            if (country) this.setLocationField('country', country);

            if (overrideVenue && venueInput && place.name && !venueInput.value) {
                venueInput.value = place.name;
            }
        },

        searchLocation() {
            const query = this.searchQuery || document.querySelector('input[name="address"]')?.value;
            if (!query || query.trim() === '') return;

            if (this.geocoder && !this.usingFallback) {
                this.geocoder.geocode({ address: query }, (results, status) => {
                    if (status === 'OK' && results[0] && results[0].geometry) {
                        const loc = results[0].geometry.location;
                        const sLat = loc.lat();
                        const sLng = loc.lng();
                        this.setCoordinates(sLat, sLng);
                        this.map.setCenter({ lat: sLat, lng: sLng });
                        this.map.setZoom(16);
                        this.placeGoogleMarker(sLat, sLng, true);
                        this.populateFormFieldsFromGooglePlace(results[0], false);
                    }
                });
            } else if (this.usingFallback) {
                // Nominatim fallback search
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data[0]) {
                        const fLat = parseFloat(data[0].lat);
                        const fLng = parseFloat(data[0].lon);
                        this.setCoordinates(fLat, fLng);
                        this.leafletMap.setView([fLat, fLng], 15);
                        this.placeLeafletMarker(fLat, fLng);
                        this.populateFormFieldsFromNominatim(data[0]);
                    }
                })
                .catch(() => {});
            }
        },

        populateFormFieldsFromNominatim(place) {
            const parts = place.address || {};
            const street = [parts.house_number, parts.road].filter(Boolean).join(' ');
            this.setLocationField('address', street || place.display_name || '');
            this.setLocationField('city', parts.city || parts.town || parts.village || parts.suburb || parts.county || '');
            this.setLocationField('country', parts.country || '');
        },

        locateCurrentPosition() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            this.isLocating = true;
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.isLocating = false;
                    const cLat = position.coords.latitude;
                    const cLng = position.coords.longitude;
                    this.setCoordinates(cLat, cLng);

                    if (!this.usingFallback && this.map) {
                        this.map.setCenter({ lat: cLat, lng: cLng });
                        this.map.setZoom(16);
                        this.placeGoogleMarker(cLat, cLng, true);
                        this.reverseGeocodeGoogle(cLat, cLng);
                    } else if (this.leafletMap) {
                        this.leafletMap.setView([cLat, cLng], 15);
                        this.placeLeafletMarker(cLat, cLng);
                        this.reverseGeocodeFallback(cLat, cLng);
                    }
                },
                (err) => {
                    this.isLocating = false;
                    console.warn('Geolocation error:', err.message);
                },
                { timeout: 10000, enableHighAccuracy: true }
            );
        },

        setCoordinates(lat, lng) {
            this.coords.lat = parseFloat(lat);
            this.coords.lng = parseFloat(lng);
        },

        clearPin() {
            this.coords.lat = null;
            this.coords.lng = null;
            if (this.marker) {
                this.marker.setMap(null);
                this.marker = null;
            }
            if (this.leafletMarker && this.leafletMap) {
                this.leafletMap.removeLayer(this.leafletMarker);
                this.leafletMarker = null;
            }
        },

        initLeafletFallback(lat, lng, zoom) {
            this.usingFallback = true;
            const loadLeafletAssets = () => {
                if (window.L) {
                    this.renderLeafletMap(lat, lng, zoom);
                    return;
                }

                if (!document.getElementById('leaflet-css')) {
                    const link = document.createElement('link');
                    link.id = 'leaflet-css';
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(link);
                }

                const script = document.createElement('script');
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = () => this.renderLeafletMap(lat, lng, zoom);
                document.head.appendChild(script);
            };

            loadLeafletAssets();
        },

        renderLeafletMap(lat, lng, zoom) {
            try {
                this.$refs.mapCanvas.innerHTML = '';
                this.leafletMap = L.map(this.$refs.mapCanvas).setView([lat, lng], zoom);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(this.leafletMap);

                if (this.hasCoords) {
                    this.placeLeafletMarker(lat, lng);
                }

                this.leafletMap.on('click', (e) => {
                    const cLat = e.latlng.lat;
                    const cLng = e.latlng.lng;
                    this.setCoordinates(cLat, cLng);
                    this.placeLeafletMarker(cLat, cLng);
                        this.reverseGeocodeFallback(cLat, cLng);
                });

                this.mapReady = true;
            } catch (err) {
                console.error('Leaflet initialization failed:', err);
                this.mapReady = true;
            }
        },

        placeLeafletMarker(lat, lng) {
            if (this.leafletMarker) {
                this.leafletMarker.setLatLng([lat, lng]);
            } else {
                this.leafletMarker = L.marker([lat, lng], { draggable: true }).addTo(this.leafletMap);
                this.leafletMarker.on('dragend', () => {
                    const pos = this.leafletMarker.getLatLng();
                    this.setCoordinates(pos.lat, pos.lng);
                    this.reverseGeocodeFallback(pos.lat, pos.lng);
                });
            }
        }
    };
}
</script>
