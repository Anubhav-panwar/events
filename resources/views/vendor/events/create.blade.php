<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Create Event</h2>
            <p class="page-subtitle">Publish a listing with schedule, audience, location, and media.</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content max-w-5xl">
            <div class="surface p-6">
                <form method="POST" action="{{ route('vendor.events.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Title</label>
                            <input name="title" value="{{ old('title') }}" class="input-field" required>
                            @error('title')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Status</label>
                            <select name="status" class="input-field" required>
                                <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                                <option value="published" @selected(old('status') === 'published')>Published</option>
                            </select>
                            @error('status')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Date</label>
                            <input type="date" name="event_date" value="{{ old('event_date') }}" class="input-field" required>
                            @error('event_date')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Venue Name</label>
                            <input name="venue_name" value="{{ old('venue_name') }}" class="input-field" placeholder="Hall / Club / Park">
                            @error('venue_name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Start Time</label>
                            <input type="time" name="start_time" value="{{ old('start_time') }}" class="input-field" required>
                            @error('start_time')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">End Time</label>
                            <input type="time" name="end_time" value="{{ old('end_time') }}" class="input-field">
                            @error('end_time')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm text-slate-700 mb-1">Address</label>
                            <input name="address" value="{{ old('address') }}" class="input-field" placeholder="Street + number">
                            @error('address')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">City</label>
                            <input name="city" value="{{ old('city') }}" class="input-field">
                            @error('city')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Country</label>
                            <input name="country" value="{{ old('country') }}" class="input-field">
                            @error('country')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Latitude</label>
                            <input name="latitude" value="{{ old('latitude') }}" class="input-field" placeholder="37.7749">
                            @error('latitude')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Longitude</label>
                            <input name="longitude" value="{{ old('longitude') }}" class="input-field" placeholder="-122.4194">
                            @error('longitude')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Category</label>
                            <select name="category_id" class="input-field">
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">For Whom (Audience Tags)</label>
                            <input name="audience" value="{{ old('audience') }}" class="input-field" placeholder="Families, Students, 18+">
                            @error('audience')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Event Type</label>
                            <select name="event_type" class="input-field" id="eventTypeField" required>
                                <option value="free" @selected(old('event_type', 'free') === 'free')>Free</option>
                                <option value="paid" @selected(old('event_type') === 'paid')>Paid</option>
                            </select>
                            @error('event_type')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Base Price</label>
                            <input type="number" step="0.01" min="0" name="base_price" value="{{ old('base_price') }}" class="input-field" id="basePriceField" placeholder="0.00">
                            @error('base_price')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Capacity</label>
                            <input type="number" min="0" name="capacity" value="{{ old('capacity') }}" class="input-field">
                            @error('capacity')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Tags</label>
                            <input name="tags" value="{{ old('tags') }}" class="input-field" placeholder="Workshop, Music, Networking">
                            @error('tags')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300" @checked(old('is_featured'))>
                                Mark as featured
                            </label>
                            @error('is_featured')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm text-slate-700 mb-1">Description</label>
                            <textarea name="description" class="input-field" rows="5">{{ old('description') }}</textarea>
                            @error('description')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm text-slate-700 mb-1">Media (Images / Videos)</label>
                            <input type="file" name="media[]" multiple class="input-field">
                            <p class="text-xs text-slate-500 mt-1">Supported: JPG, PNG, WEBP, MP4, MOV, WEBM. Max 20MB each.</p>
                            @error('media')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                            @error('media.*')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button class="btn-primary">Create Event</button>
                    </div>
                </form>
            </div>
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
