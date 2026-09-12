<x-crm-layout title="Edit Event" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-800">Vendor CRM</a>
            <span>/</span>
            <a href="{{ route('vendor.dashboard') }}#events" class="hover:text-slate-800">Events</a>
            <span>/</span>
            <span class="text-slate-900 font-bold truncate max-w-xs">Edit: {{ $event->title }}</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Header Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-950">Edit Event</h1>
                    @if($event->status === 'published')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Published
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draft
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">Update schedule, location, pricing, audience tags, and manage uploaded media files.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('events.show', $event->slug) }}" target="_blank" class="btn-secondary text-xs py-2 px-3.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    View Public Page
                </a>
                <a href="{{ route('vendor.tickets.create', $event) }}" class="btn-primary text-xs py-2 px-3.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    + Add Ticket Type
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('vendor.events.update', $event) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Card 1: Core Info --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        1. Basic Event Details
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Event Title <span class="text-red-500">*</span></label>
                        <input name="title" value="{{ old('title', $event->title) }}" class="input-field" required>
                        @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Category</label>
                        <select name="category_id" class="input-field">
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $event->category_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" class="input-field" required>
                            <option value="draft" @selected(old('status', $event->status) === 'draft')>Draft (Hidden)</option>
                            <option value="published" @selected(old('status', $event->status) === 'published')>Published (Live)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Event Admission <span class="text-red-500">*</span></label>
                        <select name="event_type" class="input-field" id="eventTypeField" required>
                            <option value="free" @selected(old('event_type', $event->event_type) === 'free')>Free</option>
                            <option value="paid" @selected(old('event_type', $event->event_type) === 'paid')>Paid</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Base Ticket Price ($)</label>
                        <input type="number" step="0.01" min="0" name="base_price" id="basePriceField" value="{{ old('base_price', $event->base_price) }}" class="input-field">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Capacity</label>
                        <input type="number" min="0" name="capacity" value="{{ old('capacity', $event->capacity) }}" class="input-field">
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_featured', $event->is_featured))>
                            Featured Event Listing
                        </label>
                    </div>
                </div>
            </div>

            {{-- Card 2: Schedule & Timing --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        2. Schedule &amp; Timing
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Event Date <span class="text-red-500">*</span></label>
                        <input type="date" name="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Start Time <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" value="{{ old('start_time', $event->start_time instanceof \Carbon\Carbon ? $event->start_time->format('H:i') : $event->start_time) }}" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">End Time</label>
                        <input type="time" name="end_time" value="{{ old('end_time', $event->end_time instanceof \Carbon\Carbon ? $event->end_time->format('H:i') : $event->end_time) }}" class="input-field">
                    </div>
                </div>
            </div>

            {{-- Card 3: Location & Venue --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        3. Venue &amp; Location
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Venue Name</label>
                        <input name="venue_name" value="{{ old('venue_name', $event->venue_name) }}" class="input-field">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Street Address</label>
                        <input name="address" value="{{ old('address', $event->address) }}" class="input-field">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
                        <input name="city" value="{{ old('city', $event->city) }}" class="input-field">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Country</label>
                        <input name="country" value="{{ old('country', $event->country) }}" class="input-field">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Latitude</label>
                        <input name="latitude" value="{{ old('latitude', $event->latitude) }}" class="input-field">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Longitude</label>
                        <input name="longitude" value="{{ old('longitude', $event->longitude) }}" class="input-field">
                    </div>
                </div>
            </div>

            {{-- Card 4: Media Gallery & Upload Additional --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            4. Photos &amp; Media Gallery
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Uploaded images are rendered dynamically on the event showcase and event cards.</p>
                    </div>
                    <span class="badge-emerald text-xs font-bold">{{ $event->media->count() }} Media Files</span>
                </div>

                {{-- Existing Media Preview --}}
                @if($event->media->count())
                    <div>
                        <p class="text-xs font-bold text-slate-700 mb-2">Current Event Media:</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($event->media as $idx => $m)
                                <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shadow-sm group">
                                    @if($m->type === 'video')
                                        <video class="w-full h-full object-cover" src="{{ $m->url }}" controls></video>
                                    @else
                                        <img src="{{ $m->url }}" class="w-full h-full object-cover" alt="{{ $m->original_name }}">
                                    @endif
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-2 text-white">
                                        <p class="text-[10px] font-medium truncate">{{ $m->original_name ?: 'Image '.($idx+1) }}</p>
                                    </div>
                                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 bg-slate-900/80 text-[10px] font-bold text-white rounded">
                                        #{{ $idx + 1 }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                        ⚠️ No media currently uploaded for this event. Upload up to 4 images below to create a dynamic gallery.
                    </div>
                @endif

                {{-- Upload Additional Media --}}
                <div class="pt-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Upload Additional Photos / Videos</label>
                    <input type="file" name="media[]" multiple class="input-field" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm">
                    <p class="text-[11px] text-slate-500 mt-1">Supported formats: JPG, PNG, WEBP, MP4, WEBM (Max 20MB each).</p>
                </div>
            </div>

            {{-- Card 5: Audience & Description --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        5. Audience &amp; Description
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Audience</label>
                        <input name="audience" value="{{ old('audience', is_array($event->audience) ? implode(', ', $event->audience) : $event->audience) }}" class="input-field">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tags</label>
                        <input name="tags" value="{{ old('tags', is_array($event->tags) ? implode(', ', $event->tags) : $event->tags) }}" class="input-field">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Description</label>
                        <textarea name="description" class="input-field" rows="5">{{ old('description', $event->description) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Save Toolbar --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('vendor.dashboard') }}" class="btn-secondary text-sm px-5 py-2.5">
                    Cancel
                </a>
                <button type="submit" class="btn-primary text-sm px-6 py-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
        </form>

        {{-- Ticket Types Section --}}
        <div class="surface p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">Configured Ticket Types</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tickets available for attendee booking and checkout.</p>
                </div>
                <a href="{{ route('vendor.tickets.create', $event) }}" class="btn-secondary text-xs py-1.5 px-3">
                    + Add Another Ticket
                </a>
            </div>

            <div class="space-y-3">
                @forelse($event->ticketTypes as $ticket)
                    <div class="flex items-center justify-between p-4 border border-slate-200 rounded-xl bg-slate-50/50 hover:bg-slate-50 transition-colors">
                        <div>
                            <p class="font-bold text-slate-900 text-sm">{{ $ticket->name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ max($ticket->quantity - $ticket->sold, 0) }} seats left of {{ $ticket->quantity }} total
                                | Sold: {{ $ticket->sold }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-emerald-700">
                                {{ (float) $ticket->price <= 0 ? 'FREE' : '$'.number_format($ticket->price, 2).' '.($ticket->currency ?: 'USD') }}
                            </span>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                {{ $ticket->sales_start?->format('M j') ?? 'Open' }} &rarr; {{ $ticket->sales_end?->format('M j') ?? 'Closing' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs">
                        No ticket tiers created yet. <a href="{{ route('vendor.tickets.create', $event) }}" class="text-emerald-700 font-bold hover:underline">Add Ticket Type</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Danger Zone & Publication Controls --}}
        <div class="surface p-6 border-slate-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                @if($event->status === 'draft')
                    <form method="POST" action="{{ route('vendor.events.publish', $event) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-emerald text-xs py-2 px-4 shadow-sm">
                            Publish Event Live
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('vendor.events.unpublish', $event) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-secondary text-xs py-2 px-4">
                            Move to Draft
                        </button>
                    </form>
                @endif
            </div>

            <form method="POST" action="{{ route('vendor.events.destroy', $event) }}" onsubmit="return confirm('Permanently delete this event? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded-lg transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Event Permanently
                </button>
            </form>
        </div>

    </div>

    <script>
        (function () {
            const eventType = document.getElementById('eventTypeField');
            const basePrice = document.getElementById('basePriceField');
            if (!eventType || !basePrice) return;

            const syncState = () => {
                const isFree = eventType.value === 'free';
                basePrice.disabled = isFree;
                if (isFree) basePrice.value = '0';
            };

            eventType.addEventListener('change', syncState);
            syncState();
        })();
    </script>
</x-crm-layout>
