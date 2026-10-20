const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const source = fs.readFileSync(path.join(__dirname, '../../resources/views/components/google-map-picker.blade.php'), 'utf8');
const script = source.split('<script>')[1].split('</script>')[0];
const place = {
    display_name: '10 Park Road, Delhi, India', name: 'Park Hall',
    address: { house_number: '10', road: 'Park Road', city: 'Delhi', country: 'India' },
};
const flush = () => new Promise(resolve => setImmediate(resolve));

function setup(fetchResponse = async () => ({ ok: true, json: async () => place })) {
    const fields = Object.fromEntries(['address', 'city', 'country', 'venue_name'].map(name => [name, {
        value: '', events: [], dispatchEvent(event) { this.events.push(event.type); },
    }]));
    const records = { requests: [], geocodes: [] };
    const form = { querySelector: selector => fields[selector.match(/name="([^"]+)"/)[1]] };
    class MapMock {
        constructor() { this.listeners = {}; records.map = this; }
        addListener(name, callback) { this.listeners[name] = callback; }
        setCenter() {} setZoom() {}
    }
    class MarkerMock {
        constructor() { this.listeners = {}; records.marker = this; }
        addListener(name, callback) { this.listeners[name] = callback; }
        setPosition() {} setMap() {}
        getPosition() { return { lat: () => 29, lng: () => 78 }; }
    }
    const google = { maps: {
        Map: MapMock, Marker: MarkerMock, Animation: { DROP: 1 },
        Geocoder: class { geocode(request, callback) { records.geocodes.push({ request, callback }); } },
    } };
    const L = {
        map() {
            records.leafletMap = {
                listeners: {}, setView() { return this; },
                on(name, callback) { this.listeners[name] = callback; },
            };
            return records.leafletMap;
        },
        tileLayer: () => ({ addTo() {} }),
        marker: () => ({ addTo() { return this; }, on() {}, setLatLng() {} }),
    };
    const context = vm.createContext({
        console, Event, AbortSignal, google, L, window: { google },
        fetch: async (url, options) => { records.requests.push(url); return fetchResponse(url, options); },
    });
    vm.runInContext(script, context);
    const picker = context.googleMapPicker({ initialLat: null, initialLng: null });
    picker.$el = { closest: () => form };
    picker.$refs = { mapCanvas: {} };
    picker.initGoogleMap(28, 77, 10);
    return { picker, records, fields };
}

test('Google map click falls back after denied geocoding and fills form fields', async () => {
    const { picker, records, fields } = setup();
    records.map.listeners.click({ latLng: { lat: () => 28, lng: () => 77 } });
    records.geocodes[0].callback([], 'REQUEST_DENIED');
    await flush();
    assert.equal(picker.coords.lat, 28);
    assert.equal(fields.address.value, '10 Park Road');
    assert.equal(fields.city.value, 'Delhi');
    assert.equal(fields.country.value, 'India');
    assert.equal(fields.venue_name.value, 'Park Hall');
    assert.deepEqual(fields.city.events, ['input', 'change']);
    assert.equal(picker.isResolvingLocation, false);
    assert.equal(picker.locationError, '');
});

test('marker drag resolves the new coordinates and preserves a manually entered venue', () => {
    const { records, fields, picker } = setup();
    picker.setCoordinates(28, 77);
    picker.placeGoogleMarker(28, 77);
    fields.venue_name.value = 'My Venue';
    records.marker.listeners.dragend();
    records.geocodes[0].callback([{
        name: 'Another venue', formatted_address: 'New street',
        address_components: [
            { types: ['locality'], long_name: 'Meerut' },
            { types: ['country'], long_name: 'India' },
        ],
    }], 'OK');
    assert.equal(picker.coords.lat, 29);
    assert.equal(fields.city.value, 'Meerut');
    assert.equal(fields.address.value, 'New street');
    assert.equal(fields.venue_name.value, 'My Venue');
});

test('an older Google result cannot overwrite a newer selected pin', () => {
    const { picker, records, fields } = setup();
    picker.setCoordinates(28, 77);
    picker.reverseGeocodeGoogle(28, 77);
    picker.setCoordinates(29, 78);
    records.geocodes[0].callback([{ formatted_address: 'Old address' }], 'OK');
    assert.equal(fields.address.value, '');
});

test('an older fallback response cannot overwrite a newer selected pin', async () => {
    let resolve;
    const { picker, fields } = setup(() => new Promise(done => { resolve = done; }));
    picker.setCoordinates(28, 77);
    const pending = picker.reverseGeocodeFallback(28, 77);
    picker.setCoordinates(29, 78);
    fields.address.value = 'New selection';
    resolve({ ok: true, json: async () => place });
    await pending;
    assert.equal(fields.address.value, 'New selection');
});

test('lookup failure leaves coordinates and existing fields intact with a visible error', async () => {
    const { picker, fields } = setup(async () => ({ ok: false }));
    fields.address.value = 'Manual address';
    picker.setCoordinates(0, 0);
    await picker.reverseGeocodeFallback(0, 0);
    assert.equal(picker.coords.lat, 0);
    assert.equal(fields.address.value, 'Manual address');
    assert.match(picker.locationError, /address could not be found/);
    assert.equal(picker.isResolvingLocation, false);
});

test('fallback search requests structured address details and fills city and country', async () => {
    const { picker, records, fields } = setup(async () => ({ ok: true, json: async () => [{ ...place, lat: '28', lon: '77' }] }));
    picker.usingFallback = true;
    picker.renderLeafletMap(28, 77, 10);
    picker.searchQuery = 'Park Hall';
    picker.searchLocation();
    await flush();
    assert.equal(fields.city.value, 'Delhi');
    assert.equal(fields.country.value, 'India');
    assert.match(records.requests[0], /search\?format=jsonv2&addressdetails=1/);
});

test('Leaflet map click fills the same location fields', async () => {
    const { picker, records, fields } = setup();
    picker.renderLeafletMap(28, 77, 10);
    records.leafletMap.listeners.click({ latlng: { lat: 28, lng: 77 } });
    await flush();
    assert.equal(fields.address.value, '10 Park Road');
    assert.equal(fields.city.value, 'Delhi');
    assert.equal(fields.country.value, 'India');
});
