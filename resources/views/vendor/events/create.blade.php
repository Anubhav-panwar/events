<x-crm-layout :title="__('Create Event')" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-800">{{ __('Vendor CRM') }}</a>
            <span>/</span>
            <a href="{{ route('vendor.dashboard') }}#events" class="hover:text-slate-800">{{ __('Events') }}</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">{{ __('New Event') }}</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Form Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950">{{ __('Create New Event') }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Configure your listing details, upload multiple dynamic photos, set location, and publish.') }}</p>
            </div>
            <a href="{{ route('vendor.dashboard') }}" class="btn-secondary text-xs py-2 px-3.5 flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to Dashboard') }}
            </a>
        </div>

        <form method="POST" action="{{ route('vendor.events.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Card 1: Core Details --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ __('1. Basic Event Details') }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Event Title') }} <span class="text-red-500">*</span></label>
                        <input name="title" value="{{ old('title') }}" class="input-field" placeholder="{{ __('e.g. Summer Acoustic Music Festival 2026') }}" required>
                        @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Category') }}</label>
                        <select name="category_id" class="input-field">
                            <option value="">{{ __('Select a Category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>{{ __($category->name) }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Publication Status') }} <span class="text-red-500">*</span></label>
                        <select name="status" class="input-field" required>
                            <option value="draft" @selected(old('status') === 'draft')>{{ __('Draft (Hidden from Public)') }}</option>
                            <option value="published" @selected(old('status', 'published') === 'published')>{{ __('Published (Live Now)') }}</option>
                        </select>
                        @error('status')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Admission Type') }} <span class="text-red-500">*</span></label>
                        <select name="event_type" class="input-field" id="eventTypeField" required>
                            <option value="free" @selected(old('event_type', 'free') === 'free')>{{ __('Free Admission') }}</option>
                            <option value="paid" @selected(old('event_type') === 'paid')>{{ __('Paid Tickets') }}</option>
                        </select>
                        @error('event_type')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Base Ticket Price ($)') }}</label>
                        <input type="number" step="0.01" min="0" name="base_price" value="{{ old('base_price') }}" class="input-field" id="basePriceField" placeholder="0.00">
                        @error('base_price')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Total Capacity') }}</label>
                        <input type="number" min="0" name="capacity" value="{{ old('capacity') }}" class="input-field" placeholder="{{ __('e.g. 250') }}">
                        @error('capacity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_featured'))>
                            {{ __('Mark as Featured Event (Highlights on Home Page)') }}
                        </label>
                    </div>
                </div>
            </div>

            {{-- Card 2: Date & Timing --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ __('2. Schedule & Timing') }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Event Date') }} <span class="text-red-500">*</span></label>
                        <input type="date" name="event_date" value="{{ old('event_date', date('Y-m-d', strtotime('+7 days'))) }}" class="input-field" required>
                        @error('event_date')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Start Time') }} <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" value="{{ old('start_time', '18:00') }}" class="input-field" required>
                        @error('start_time')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('End Time') }}</label>
                        <input type="time" name="end_time" value="{{ old('end_time', '21:00') }}" class="input-field">
                        @error('end_time')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-start gap-3 bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/60">
                    <input type="hidden" name="is_date_confirmed" value="0">
                    <input type="checkbox" id="is_date_confirmed" name="is_date_confirmed" value="1" class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_date_confirmed', true))>
                    <div>
                        <label for="is_date_confirmed" class="text-xs font-bold text-slate-800 cursor-pointer block">
                            {{ __('Confirmed Event Schedule') }}
                        </label>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                            {{ __('Keep checked if this date and time are confirmed. If unchecked, the event date will be marked as tentative and calendar integration (Google Calendar & .ICS downloads) will remain unavailable until confirmed.') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Card 3: Location & Venue --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ __('3. Venue & Location') }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Venue Name') }}</label>
                        <input name="venue_name" value="{{ old('venue_name') }}" class="input-field" placeholder="{{ __('e.g. Royal Hall, City Center Park, Tech Campus') }}">
                        @error('venue_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Street Address') }}</label>
                        <input name="address" value="{{ old('address') }}" class="input-field" placeholder="{{ __('123 Main Boulevard') }}">
                        @error('address')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('City') }}</label>
                        <input name="city" value="{{ old('city') }}" class="input-field" placeholder="{{ __('e.g. New York') }}">
                        @error('city')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Country') }}</label>
                        <input name="country" value="{{ old('country', 'USA') }}" class="input-field" placeholder="{{ __('Country') }}">
                        @error('country')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Interactive Google Map Location Picker (coordinates stored internally) --}}
                    <div class="md:col-span-2 pt-2 border-t border-slate-100">
                        <x-google-map-picker
                            :latitude="old('latitude')"
                            :longitude="old('longitude')"
                        />
                    </div>
                </div>
            </div>

            {{-- Card 4: Media Gallery & Uploads with Live Preview --}}
            <div class="surface p-6 space-y-4" x-data="{
                previews: [],
                errorMsg: null,
                maxFiles: 15,
                maxImageBytes: 30 * 1024 * 1024,
                maxVideoBytes: 100 * 1024 * 1024,
                handleFileSelect(e) {
                    this.errorMsg = null;
                    const input = e.target;
                    const files = Array.from(input.files || []);
                    if (!files.length) return;

                    if (files.length > this.maxFiles) {
                        this.errorMsg = '{{ __('Maximum :max media files can be uploaded at once.', ['max' => 15]) }}';
                    }

                    const selected = files.slice(0, this.maxFiles);
                    this.previews = [];

                    selected.forEach((file, index) => {
                        const isVideo = file.type.startsWith('video/') || /\.(mp4|mov|webm|m4v|mkv)$/i.test(file.name);
                        const isOversized = isVideo ? (file.size > this.maxVideoBytes) : (file.size > this.maxImageBytes);
                        const limitText = isVideo ? '100 MB' : '30 MB';

                        if (isOversized) {
                            this.errorMsg = file.name + ' {{ __('exceeds the maximum size limit of') }} ' + limitText + '.';
                        }

                        if (!isVideo && file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (ev) => {
                                this.previews.push({
                                    id: index,
                                    name: file.name,
                                    size: (file.size / (1024 * 1024)).toFixed(1) + ' MB',
                                    url: ev.target.result,
                                    isVideo: false,
                                    isOversized: isOversized
                                });
                            };
                            reader.readAsDataURL(file);
                        } else {
                            this.previews.push({
                                id: index,
                                name: file.name,
                                size: (file.size / (1024 * 1024)).toFixed(1) + ' MB',
                                isVideo: true,
                                isOversized: isOversized
                            });
                        }
                    });
                }
            }">
                <div class="border-b border-slate-100 pb-3 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ __('4. Photos & Media Gallery (Dynamic Showcase)') }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Upload up to 15 photos & video highlights to create an immersive dynamic showcase on your event page.') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge bg-slate-100 text-slate-700 text-xs font-bold">{{ __('Max 15 Media') }}</span>
                        <span x-show="previews.length > 0" class="badge-emerald text-xs font-bold" x-text="previews.length + ' / 15 {{ __('selected') }}'"></span>
                    </div>
                </div>

                {{-- Client-side validation warning banner --}}
                <div x-show="errorMsg" class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs flex items-center justify-between" style="display: none;">
                    <span class="font-medium" x-text="errorMsg"></span>
                    <button type="button" @click="errorMsg = null" class="text-red-500 hover:text-red-700 font-bold ml-2">&times;</button>
                </div>

                <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-2xl p-8 text-center bg-slate-50/50 hover:bg-emerald-50/30 transition-all cursor-pointer relative">
                    <input type="file"
                           id="eventMediaInput"
                           name="media[]"
                           multiple
                           @change="handleFileSelect($event)"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                           accept="image/jpeg,image/png,image/webp,image/avif,image/gif,video/mp4,video/webm,video/quicktime">

                    <div class="flex flex-col items-center justify-center pointer-events-none">
                        <div class="w-12 h-12 bg-white rounded-2xl border border-slate-200 flex items-center justify-center text-emerald-600 mb-3 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-900">{{ __('Click to browse or drop photos and videos here') }}</p>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ __('Photos: JPG, PNG, WEBP, AVIF, GIF (up to 30MB each)') }} &bull; {{ __('Videos: MP4, MOV, WEBM (up to 100MB each)') }}
                        </p>
                        <p class="text-[11px] font-semibold text-emerald-700 mt-1.5">
                            {{ __('Up to 15 media files allowed per event') }}
                        </p>
                    </div>
                </div>

                {{-- Live Image Previews --}}
                <div x-show="previews.length > 0" class="space-y-2 pt-2">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold text-slate-700">{{ __('Selected Media Preview:') }}</p>
                        <span class="text-[11px] text-slate-500" x-text="previews.length + ' / 15 {{ __('slots used') }}'"></span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                        <template x-for="(p, idx) in previews" :key="idx">
                            <div class="relative aspect-square rounded-xl overflow-hidden border bg-slate-100 shadow-sm flex flex-col justify-end p-2 transition-all"
                                 :class="p.isOversized ? 'border-red-500 ring-2 ring-red-400' : 'border-slate-200'">
                                <template x-if="p.url">
                                    <img :src="p.url" class="absolute inset-0 w-full h-full object-cover" :alt="p.name">
                                </template>
                                <template x-if="p.isVideo">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 text-white p-2 text-center">
                                        <span class="text-2xl mb-1">🎬</span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">{{ __('Video') }}</span>
                                    </div>
                                </template>

                                <div class="relative z-10 bg-black/80 backdrop-blur-sm px-2 py-1 rounded text-[10px] text-white">
                                    <div class="truncate font-medium" x-text="p.name"></div>
                                    <div class="flex items-center justify-between text-[9px] text-slate-300 mt-0.5">
                                        <span x-text="p.isVideo ? '🎬 Video' : '📷 Photo'"></span>
                                        <span :class="p.isOversized ? 'text-red-400 font-bold' : ''" x-text="p.size"></span>
                                    </div>
                                </div>
                                <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 bg-emerald-600 text-[10px] font-bold text-white rounded shadow-sm" x-text="'#' + (idx + 1)"></span>
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
                        {{ __('5. Audience & Description') }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span>{{ __('Target Audience') }} <span class="text-red-500">*</span></span>
                            <span class="text-[11px] font-normal text-slate-500">{{ __('Select one or more categories') }}</span>
                        </label>
                        @php
                            $audienceOptions = [
                                \App\Models\Event::AUDIENCE_KIDS => [
                                    'label' => __('Kids'),
                                    'desc'  => __('Events intended for children.'),
                                    'icon'  => '🎈',
                                ],
                                \App\Models\Event::AUDIENCE_TEENS => [
                                    'label' => __('Teens'),
                                    'desc'  => __('Events intended for teenagers.'),
                                    'icon'  => '🎮',
                                ],
                                \App\Models\Event::AUDIENCE_ADULTS => [
                                    'label' => __('Adults'),
                                    'desc'  => __('Events intended for adults.'),
                                    'icon'  => '🍸',
                                ],
                                \App\Models\Event::AUDIENCE_ALL_AGES => [
                                    'label' => __('All Ages'),
                                    'desc'  => __('Events suitable for everyone.'),
                                    'icon'  => '🌟',
                                ],
                            ];
                            $selectedAudiences = old('audience', []);
                            if (is_string($selectedAudiences)) {
                                $selectedAudiences = array_map('trim', explode(',', $selectedAudiences));
                            }
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            @foreach($audienceOptions as $key => $opt)
                                @php $checked = in_array($key, (array)$selectedAudiences, true); @endphp
                                <label class="relative flex items-start p-3.5 rounded-xl border cursor-pointer transition-all hover:border-emerald-400 hover:shadow-sm {{ $checked ? 'border-emerald-500 bg-emerald-50/60 ring-1 ring-emerald-500' : 'border-slate-200 bg-white' }}">
                                    <input type="checkbox" name="audience[]" value="{{ $key }}" class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked($checked)>
                                    <div class="ml-3 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                             <span class="text-sm">{{ $opt['icon'] }}</span>
                                            <span class="text-sm font-bold text-slate-900">{{ $opt['label'] }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $opt['desc'] }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('audience')<p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                        @error('audience.*')<p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Tags (Comma separated)') }}</label>
                        <input name="tags" value="{{ old('tags') }}" class="input-field" placeholder="Workshop, Music, Networking, Outdoor">
                        @error('tags')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Event Description') }}</label>
                        <textarea name="description" class="input-field" rows="5" placeholder="{{ __('Describe the activities, schedule, what attendees should bring, highlights, and venue info...') }}">{{ old('description') }}</textarea>
                        @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('vendor.dashboard') }}" class="btn-secondary text-sm px-5 py-2.5">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn-emerald text-sm px-7 py-2.5 shadow-md shadow-emerald-950/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ __('Create & Publish Event') }}
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
