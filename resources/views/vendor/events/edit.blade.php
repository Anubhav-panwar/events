<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="page-title">Edit Event</h2>
                <p class="page-subtitle">Update details, publish state, media, and ticket setup.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('events.show', $event->slug) }}" class="btn-secondary">View</a>
                <a href="{{ route('vendor.tickets.create', $event) }}" class="btn-primary">Add Ticket Type</a>
            </div>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content max-w-6xl space-y-6">
            @if (session('status'))
                <div class="surface p-4 text-emerald-700">{{ session('status') }}</div>
            @endif

            <div class="surface p-6">
                <form method="POST" action="{{ route('vendor.events.update', $event) }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Title</label>
                            <input name="title" value="{{ old('title', $event->title) }}" class="input-field" required>
                            @error('title')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Status</label>
                            <select name="status" class="input-field" required>
                                <option value="draft" @selected(old('status', $event->status) === 'draft')>Draft</option>
                                <option value="published" @selected(old('status', $event->status) === 'published')>Published</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Date</label>
                            <input type="date" name="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}" class="input-field" required>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Venue Name</label>
                            <input name="venue_name" value="{{ old('venue_name', $event->venue_name) }}" class="input-field">
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Start Time</label>
                            <input type="time" name="start_time" value="{{ old('start_time', $event->start_time?->format('H:i')) }}" class="input-field" required>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">End Time</label>
                            <input type="time" name="end_time" value="{{ old('end_time', $event->end_time?->format('H:i')) }}" class="input-field">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm text-slate-700 mb-1">Address</label>
                            <input name="address" value="{{ old('address', $event->address) }}" class="input-field">
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">City</label>
                            <input name="city" value="{{ old('city', $event->city) }}" class="input-field">
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Country</label>
                            <input name="country" value="{{ old('country', $event->country) }}" class="input-field">
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Latitude</label>
                            <input name="latitude" value="{{ old('latitude', $event->latitude) }}" class="input-field">
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Longitude</label>
                            <input name="longitude" value="{{ old('longitude', $event->longitude) }}" class="input-field">
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Category</label>
                            <select name="category_id" class="input-field">
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) old('category_id', $event->category_id) === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">For Whom (Audience Tags)</label>
                            <input name="audience" value="{{ old('audience', implode(', ', $event->audience ?? [])) }}" class="input-field">
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Event Type</label>
                            <select name="event_type" class="input-field" id="eventTypeField" required>
                                <option value="free" @selected(old('event_type', $event->event_type) === 'free')>Free</option>
                                <option value="paid" @selected(old('event_type', $event->event_type) === 'paid')>Paid</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Base Price</label>
                            <input type="number" step="0.01" min="0" name="base_price" id="basePriceField" value="{{ old('base_price', $event->base_price) }}" class="input-field">
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Capacity</label>
                            <input type="number" min="0" name="capacity" value="{{ old('capacity', $event->capacity) }}" class="input-field">
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Tags</label>
                            <input name="tags" value="{{ old('tags', implode(', ', $event->tags ?? [])) }}" class="input-field">
                        </div>

                        <div class="md:col-span-2">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300" @checked(old('is_featured', $event->is_featured))>
                                Mark as featured
                            </label>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm text-slate-700 mb-1">Description</label>
                            <textarea name="description" class="input-field" rows="5">{{ old('description', $event->description) }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm text-slate-700 mb-1">Upload Additional Media</label>
                            <input type="file" name="media[]" multiple class="input-field">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button class="btn-primary">Save Changes</button>
                    </div>
                </form>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
                    <div class="flex gap-2">
                        @if($event->status === 'draft')
                            <form method="POST" action="{{ route('vendor.events.publish', $event) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn-primary">Publish</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('vendor.events.unpublish', $event) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn-secondary">Move to Draft</button>
                            </form>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('vendor.events.destroy', $event) }}" onsubmit="return confirm('Delete this event permanently?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn-secondary text-red-700 border-red-200 hover:border-red-300">Delete Event</button>
                    </form>
                </div>
            </div>

            <div class="surface p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-3">Ticket Types</h3>
                <div class="space-y-3">
                    @forelse($event->ticketTypes as $ticket)
                        <div class="border border-slate-200 rounded-xl p-4">
                            <div class="font-semibold text-slate-900">{{ $ticket->name }}</div>
                            <div class="text-sm text-slate-600 mt-1">
                                {{ number_format($ticket->price, 2) }} {{ $ticket->currency }}
                                | {{ max($ticket->quantity - $ticket->sold, 0) }} left of {{ $ticket->quantity }}
                            </div>
                            <div class="text-xs text-slate-500 mt-1">
                                Sales window:
                                {{ $ticket->sales_start?->format('Y-m-d H:i') ?? 'Anytime' }}
                                ->
                                {{ $ticket->sales_end?->format('Y-m-d H:i') ?? 'Until sold out' }}
                            </div>
                        </div>
                    @empty
                        <div class="text-slate-600">No ticket types yet.</div>
                    @endforelse
                </div>
            </div>

            @if($event->media->count())
                <div class="surface p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-3">Media Gallery</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($event->media as $m)
                            @if($m->type === 'video')
                                <video class="rounded-xl border border-slate-200" controls src="{{ Storage::disk($m->disk)->url($m->path) }}"></video>
                            @else
                                <img class="rounded-xl border border-slate-200" src="{{ Storage::disk($m->disk)->url($m->path) }}" alt="{{ $m->original_name }}">
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

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
</x-app-layout>
