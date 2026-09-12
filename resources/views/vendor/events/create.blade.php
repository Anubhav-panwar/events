<x-crm-layout title="Create Event" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-800">Vendor CRM</a>
            <span>/</span>
            <a href="{{ route('vendor.dashboard') }}#events" class="hover:text-slate-800">Events</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">New Event</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Form Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950">Create New Event</h1>
                <p class="text-xs text-slate-500 mt-1">Configure your listing details, upload multiple dynamic photos, set location, and publish.</p>
            </div>
            <a href="{{ route('vendor.dashboard') }}" class="btn-secondary text-xs py-2 px-3.5 flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Dashboard
            </a>
        </div>

        <form method="POST" action="{{ route('vendor.events.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Card 1: Core Details --}}
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
                        <input name="title" value="{{ old('title') }}" class="input-field" placeholder="e.g. Summer Acoustic Music Festival 2026" required>
                        @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Category</label>
                        <select name="category_id" class="input-field">
                            <option value="">Select a Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Publication Status <span class="text-red-500">*</span></label>
                        <select name="status" class="input-field" required>
                            <option value="draft" @selected(old('status') === 'draft')>Draft (Hidden from Public)</option>
                            <option value="published" @selected(old('status', 'published') === 'published')>Published (Live Now)</option>
                        </select>
                        @error('status')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Admission Type <span class="text-red-500">*</span></label>
                        <select name="event_type" class="input-field" id="eventTypeField" required>
                            <option value="free" @selected(old('event_type', 'free') === 'free')>Free Admission</option>
                            <option value="paid" @selected(old('event_type') === 'paid')>Paid Tickets</option>
                        </select>
                        @error('event_type')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Base Ticket Price ($)</label>
                        <input type="number" step="0.01" min="0" name="base_price" value="{{ old('base_price') }}" class="input-field" id="basePriceField" placeholder="0.00">
                        @error('base_price')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Total Capacity</label>
                        <input type="number" min="0" name="capacity" value="{{ old('capacity') }}" class="input-field" placeholder="e.g. 250">
                        @error('capacity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_featured'))>
                            Mark as Featured Event (Highlights on Home Page)
                        </label>
                    </div>
                </div>
            </div>

            {{-- Card 2: Date & Timing --}}
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
                        <input type="date" name="event_date" value="{{ old('event_date', date('Y-m-d', strtotime('+7 days'))) }}" class="input-field" required>
                        @error('event_date')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Start Time <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" value="{{ old('start_time', '18:00') }}" class="input-field" required>
                        @error('start_time')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">End Time</label>
                        <input type="time" name="end_time" value="{{ old('end_time', '21:00') }}" class="input-field">
                        @error('end_time')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
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
                        <input name="venue_name" value="{{ old('venue_name') }}" class="input-field" placeholder="e.g. Royal Hall, City Center Park, Tech Campus">
                        @error('venue_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Street Address</label>
                        <input name="address" value="{{ old('address') }}" class="input-field" placeholder="123 Main Boulevard">
                        @error('address')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
                        <input name="city" value="{{ old('city') }}" class="input-field" placeholder="e.g. New York">
                        @error('city')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Country</label>
                        <input name="country" value="{{ old('country', 'USA') }}" class="input-field" placeholder="Country">
                        @error('country')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Latitude (Optional for Map)</label>
                        <input name="latitude" value="{{ old('latitude') }}" class="input-field" placeholder="40.7128">
                        @error('latitude')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Longitude (Optional for Map)</label>
                        <input name="longitude" value="{{ old('longitude') }}" class="input-field" placeholder="-74.0060">
                        @error('longitude')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Card 4: Media Gallery & Uploads with Live Preview --}}
            <div class="surface p-6 space-y-4" x-data="{
                previews: [],
                handleFileSelect(e) {
                    this.previews = [];
                    const files = e.target.files;
                    if (!files) return;
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (ev) => {
                                this.previews.push({
                                    name: file.name,
                                    size: (file.size / (1024*1024)).toFixed(1) + ' MB',
                                    url: ev.target.result
                                });
                            };
                            reader.readAsDataURL(file);
                        } else {
                            this.previews.push({
                                name: file.name,
                                size: (file.size / (1024*1024)).toFixed(1) + ' MB',
                                isVideo: true
                            });
                        }
                    }
                }
            }">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            4. Photos &amp; Media Gallery (Dynamic Showcase)
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Upload 4 high-quality photos (JPG, PNG, WEBP) to create a dynamic multi-photo gallery on the event page.</p>
                    </div>
                    <span x-show="previews.length > 0" class="badge-emerald text-xs font-bold" x-text="previews.length + ' file(s) selected'"></span>
                </div>

                <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-2xl p-8 text-center bg-slate-50/50 hover:bg-emerald-50/30 transition-all cursor-pointer relative">
                    <input type="file"
                           name="media[]"
                           multiple
                           @change="handleFileSelect($event)"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                           accept="image/jpeg,image/png,image/webp,video/mp4,video/webm">

                    <div class="flex flex-col items-center justify-center pointer-events-none">
                        <div class="w-12 h-12 bg-white rounded-2xl border border-slate-200 flex items-center justify-center text-emerald-600 mb-3 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-900">Click to browse or drop images here</p>
                        <p class="text-xs text-slate-500 mt-1">Select up to 4 images. JPG, PNG, WEBP, MP4 supported (Max 20MB each).</p>
                    </div>
                </div>

                {{-- Live Image Previews --}}
                <div x-show="previews.length > 0" class="space-y-2 pt-2">
                    <p class="text-xs font-bold text-slate-700">Selected Uploads Preview:</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <template x-for="(p, idx) in previews" :key="idx">
                            <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shadow-sm flex flex-col justify-end p-2">
                                <template x-if="p.url">
                                    <img :src="p.url" class="absolute inset-0 w-full h-full object-cover" :alt="p.name">
                                </template>
                                <template x-if="p.isVideo">
                                    <div class="absolute inset-0 flex items-center justify-center bg-slate-900 text-white text-xs font-bold">
                                        🎬 Video
                                    </div>
                                </template>
                                <div class="relative z-10 bg-black/75 backdrop-blur-sm px-2 py-1 rounded text-[10px] text-white truncate">
                                    <span x-text="p.name"></span>
                                </div>
                                <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 bg-emerald-600 text-[10px] font-bold text-white rounded shadow" x-text="'#' + (idx + 1)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                @error('media')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                @error('media.*')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
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
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Audience</label>
                        <input name="audience" value="{{ old('audience') }}" class="input-field" placeholder="Families, Tech enthusiasts, 18+, Students">
                        @error('audience')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tags (Comma separated)</label>
                        <input name="tags" value="{{ old('tags') }}" class="input-field" placeholder="Workshop, Music, Networking, Outdoor">
                        @error('tags')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Event Description</label>
                        <textarea name="description" class="input-field" rows="5" placeholder="Describe the activities, schedule, what attendees should bring, highlights, and venue info...">{{ old('description') }}</textarea>
                        @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('vendor.dashboard') }}" class="btn-secondary text-sm px-5 py-2.5">
                    Cancel
                </a>
                <button type="submit" class="btn-emerald text-sm px-7 py-2.5 shadow-md shadow-emerald-950/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Create &amp; Publish Event
                </button>
            </div>
        </form>

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
