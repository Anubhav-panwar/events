<x-crm-layout :title="__('Edit Event')" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-800">{{ __('Vendor CRM') }}</a>
            <span>/</span>
            <a href="{{ route('vendor.dashboard') }}#events" class="hover:text-slate-800">{{ __('Events') }}</a>
            <span>/</span>
            <span class="text-slate-900 font-bold truncate max-w-xs">{{ __('Edit:') }} {{ __($event->title) }}</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Header Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-950">{{ __('Edit Event') }}</h1>
                    @if($event->status === 'draft')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> {{ __('Draft') }}
                        </span>
                    @elseif($event->isDatesTba())
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> {{ __('Published – Dates TBA') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ __('Published – Confirmed') }}
                        </span>
                    @endif

                    @if($event->isBookingEligible())
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-200">
                            {{ __('Booking Open') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-medium border border-slate-200">
                            {{ __('Booking Disabled') }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ __('Update schedule, location, pricing, audience tags, and manage uploaded media files.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('events.show', $event->slug) }}" target="_blank" class="btn-secondary text-xs py-2 px-3.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    {{ __('View Public Page') }}
                </a>
                <a href="{{ route('vendor.tickets.create', $event) }}" class="btn-primary text-xs py-2 px-3.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    {{ __('+ Add Ticket Type') }}
                </a>
            </div>
        </div>

        @if($event->isDatesTba())
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="flex-1">
                    <p class="font-bold text-sm text-amber-950">{{ __('Upcoming Event (Dates To Be Announced)') }}</p>
                    <p class="mt-0.5 leading-relaxed">{{ __('This event is listed as TBA. Ticket booking and calendar exports are disabled. Customers can subscribe to "Notify Me" reminders.') }}</p>
                    @php
                        $activeRemindersCount = $event->reminders()->active()->count();
                    @endphp
                    <p class="mt-1 font-semibold text-amber-800">
                        {{ trans_choice('{0} No users have subscribed to reminders yet.|{1} 1 user is waiting for date announcement.|[2,*] :count users are waiting for date announcement.', $activeRemindersCount, ['count' => $activeRemindersCount]) }}
                    </p>
                    <p class="mt-0.5 text-slate-500">
                        {{ __('When you enter confirmed dates/times and save with "Confirmed Event Schedule" checked, an announcement email will automatically be dispatched to all subscribers.') }}
                    </p>
                </div>
            </div>
        @elseif($event->dates_announced_at)
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="flex-1">
                    <p class="font-bold text-sm text-emerald-950">{{ __('Date Announcement Email Sent') }}</p>
                    <p class="mt-0.5 leading-relaxed">
                        {{ __('Confirmed date announcement email was dispatched to subscribers on :date.', ['date' => $event->dates_announced_at->format('M d, Y H:i')]) }}
                    </p>
                    @php
                        $notifiedCount = $event->reminders()->whereIn('notification_status', [\App\Models\EventReminder::STATUS_NOTIFIED, 'sent'])->count();
                        $failedCount = $event->reminders()->where('notification_status', \App\Models\EventReminder::STATUS_FAILED)->count();
                        $queuedCount = $event->reminders()->whereIn('notification_status', [\App\Models\EventReminder::STATUS_QUEUED, \App\Models\EventReminder::STATUS_ATTEMPTED])->count();
                    @endphp
                    <div class="mt-2 flex items-center gap-3">
                        <span class="font-semibold text-emerald-800">{{ $notifiedCount }} {{ __('messages accepted by mail transport') }}</span>
                        @if($queuedCount > 0)
                            <span class="font-semibold text-blue-700">{{ $queuedCount }} {{ __('queued or attempting') }}</span>
                        @endif
                        @if($failedCount > 0)
                            <span class="font-bold text-red-600">{{ $failedCount }} {{ __('failed deliveries') }}</span>
                            <form method="POST" action="{{ route('vendor.events.reminders.retry', $event) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-red-700 underline hover:text-red-900 ml-2">
                                    {{ __('Retry Failed Deliveries') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('vendor.events.update', $event) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Card 1: Core Info --}}
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
                        <input name="title" value="{{ old('title', $event->getRawOriginal('title') ?? $event->title) }}" class="input-field" required>
                        @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Category') }}</label>
                        <select name="category_id" class="input-field">
                            <option value="">{{ __('Select category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $event->category_id) === $category->id)>{{ __($category->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Status') }} <span class="text-red-500">*</span></label>
                        <select name="status" class="input-field" required>
                            <option value="draft" @selected(old('status', $event->status) === 'draft')>{{ __('Draft (Hidden)') }}</option>
                            <option value="published" @selected(old('status', $event->status) === 'published')>{{ __('Published (Live)') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Event Admission') }}</label>
                        <select name="event_type" class="input-field" id="eventTypeField">
                            <option value="">{{ __('To Be Announced (Pricing TBA)') }}</option>
                            <option value="free" @selected(old('event_type', $event->event_type) === 'free')>{{ __('Free') }}</option>
                            <option value="paid" @selected(old('event_type', $event->event_type) === 'paid')>{{ __('Paid') }}</option>
                        </select>
                        @error('event_type')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Base Ticket Price ($)') }}</label>
                        <input type="number" step="0.01" min="0" name="base_price" id="basePriceField" value="{{ old('base_price', $event->base_price) }}" class="input-field" placeholder="0.00 (TBA if left empty)">
                        @error('base_price')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Capacity') }}</label>
                        <input type="number" min="0" name="capacity" value="{{ old('capacity', $event->capacity) }}" class="input-field">
                        @error('capacity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_featured', $event->is_featured))>
                            {{ __('Featured Event Listing') }}
                        </label>
                    </div>
                </div>
            </div>

            {{-- Card 2: Schedule & Timing --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ __('2. Schedule & Timing') }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Leave empty if event date is To Be Announced (TBA).') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Event Date') }} <span class="text-xs font-normal text-slate-400">({{ __('Optional / TBA') }})</span></label>
                        <input type="date" name="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}" class="input-field">
                        @error('event_date')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Start Time') }} <span class="text-xs font-normal text-slate-400">({{ __('Optional') }})</span></label>
                        <input type="time" name="start_time" value="{{ old('start_time', $event->start_time instanceof \Carbon\Carbon ? $event->start_time->format('H:i') : $event->start_time) }}" class="input-field">
                        @error('start_time')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('End Time') }} <span class="text-xs font-normal text-slate-400">({{ __('Optional') }})</span></label>
                        <input type="time" name="end_time" value="{{ old('end_time', $event->end_time instanceof \Carbon\Carbon ? $event->end_time->format('H:i') : $event->end_time) }}" class="input-field">
                        @error('end_time')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-start gap-3 bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/60">
                    <input type="hidden" name="is_date_confirmed" value="0">
                    <input type="checkbox" id="is_date_confirmed" name="is_date_confirmed" value="1" class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_date_confirmed', $event->is_date_confirmed))>
                    <div>
                        <label for="is_date_confirmed" class="text-xs font-bold text-slate-800 cursor-pointer block">
                            {{ __('Confirmed Event Schedule & Ready for Booking') }}
                        </label>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                            {{ __('Check this when the event date, start time, and booking configuration are confirmed. Saving with confirmed dates will trigger automatic email announcements to all users waiting for dates.') }}
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
                        <input name="venue_name" value="{{ old('venue_name', $event->getRawOriginal('venue_name') ?? $event->venue_name) }}" class="input-field">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Street Address') }}</label>
                        <input name="address" value="{{ old('address', $event->getRawOriginal('address') ?? $event->address) }}" class="input-field">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('City') }}</label>
                        <input name="city" value="{{ old('city', $event->getRawOriginal('city') ?? $event->city) }}" class="input-field">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Country') }}</label>
                        <input name="country" value="{{ old('country', $event->getRawOriginal('country') ?? $event->country) }}" class="input-field">
                    </div>

                    {{-- Interactive Google Map Location Picker (coordinates stored internally) --}}
                    <div class="md:col-span-2 pt-2 border-t border-slate-100">
                        <x-google-map-picker
                            :latitude="old('latitude', $event->latitude)"
                            :longitude="old('longitude', $event->longitude)"
                            :venueName="$event->venue_name"
                            :address="$event->address"
                            :city="$event->city"
                            :country="$event->country"
                        />
                    </div>
                </div>
            </div>

            {{-- Card 4: Media Gallery & Upload Additional --}}
            <div class="surface p-6 space-y-4" x-data="{
                previews: [],
                errorMsg: null,
                maxAllowed: {{ max(0, 15 - $event->media->count()) }},
                handleFileSelect(e) {
                    this.errorMsg = null;
                    const files = Array.from(e.target.files || []);
                    if (!files.length) return;

                    if (files.length > this.maxAllowed) {
                        this.errorMsg = '{{ __('You can upload at most :available more media files.', ['available' => max(0, 15 - $event->media->count())]) }}';
                    }

                    const selected = files.slice(0, this.maxAllowed);
                    this.previews = [];

                    selected.forEach((file, index) => {
                        const isVideo = file.type.startsWith('video/') || /\.(mp4|mov|webm|m4v|mkv)$/i.test(file.name);
                        const isOversized = isVideo ? (file.size > 100 * 1024 * 1024) : (file.size > 30 * 1024 * 1024);
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
                            {{ __('4. Photos & Media Gallery') }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Uploaded images and videos are rendered dynamically in high-resolution showcases on your event page.') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge {{ $event->media->count() >= 15 ? 'bg-amber-100 text-amber-800' : 'badge-emerald' }} text-xs font-bold">
                            {{ $event->media->count() }} / 15 {{ __('Media Files') }}
                        </span>
                        <span class="text-xs text-slate-500">({{ max(0, 15 - $event->media->count()) }} {{ __('slots available') }})</span>
                    </div>
                </div>

                {{-- Existing Media Preview with Delete Action --}}
                @if($event->media->count())
                    <div>
                        <p class="text-xs font-bold text-slate-700 mb-2">{{ __('Current Event Media:') }}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                            @foreach($event->media as $idx => $m)
                                <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shadow-sm group flex flex-col justify-end p-2">
                                    @if($m->type === 'video')
                                        <div class="absolute inset-0 bg-slate-900 flex items-center justify-center">
                                            <video class="w-full h-full object-cover" src="{{ $m->url }}" preload="metadata" playsinline controls></video>
                                        </div>
                                    @else
                                        <img src="{{ $m->url }}" class="absolute inset-0 w-full h-full object-cover" alt="{{ $m->original_name }}">
                                    @endif

                                    <div class="relative z-10 bg-black/80 backdrop-blur-sm px-2 py-1 rounded text-[10px] text-white">
                                        <p class="font-medium truncate">{{ $m->original_name ?: __('Media ').($idx+1) }}</p>
                                        <p class="text-[9px] text-slate-300">{{ $m->type === 'video' ? '🎬 Video' : '📷 Photo' }} @if($m->size) &bull; {{ number_format($m->size / (1024*1024), 1) }} MB @endif</p>
                                    </div>

                                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 bg-slate-900/80 text-[10px] font-bold text-white rounded z-20">
                                        #{{ $idx + 1 }}
                                    </span>

                                    {{-- Delete Media Button --}}
                                    <button type="submit" form="delete-event-media-{{ $m->id }}" onclick="return confirm('{{ __('Are you sure you want to delete this media file?') }}')" class="absolute top-1.5 right-1.5 z-20 w-6 h-6 bg-red-600/90 hover:bg-red-700 text-white rounded-md flex items-center justify-center shadow transition-colors" title="{{ __('Delete media file') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                        {{ __('⚠️ No media currently uploaded for this event. Upload up to 15 photos and videos below to create a dynamic gallery.') }}
                    </div>
                @endif

                {{-- Client-side validation warning banner --}}
                <div x-show="errorMsg" class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs flex items-center justify-between" style="display: none;">
                    <span class="font-medium" x-text="errorMsg"></span>
                    <button type="button" @click="errorMsg = null" class="text-red-500 hover:text-red-700 font-bold ml-2">&times;</button>
                </div>

                {{-- Upload Additional Media --}}
                @if($event->media->count() < 15)
                    <div class="pt-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            {{ __('Upload Additional Photos / Videos') }}
                            <span class="font-normal text-slate-500">({{ max(0, 15 - $event->media->count()) }} {{ __('slots available') }})</span>
                        </label>
                        <input type="file"
                               name="media[]"
                               multiple
                               @change="handleFileSelect($event)"
                               class="input-field"
                               accept="image/jpeg,image/png,image/webp,image/avif,image/gif,video/mp4,video/webm,video/quicktime">
                        <p class="text-[11px] text-slate-500 mt-1">
                            {{ __('Photos: JPG, PNG, WEBP, AVIF (up to 30MB) • Videos: MP4, MOV, WEBM (up to 100MB). Max 15 total per event.') }}
                        </p>

                        {{-- Live Previews for Newly Selected Files --}}
                        <div x-show="previews.length > 0" class="space-y-2 pt-3">
                            <p class="text-xs font-bold text-slate-700">{{ __('Newly Selected Files (to be saved):') }}</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                                <template x-for="(p, idx) in previews" :key="idx">
                                    <div class="relative aspect-square rounded-xl overflow-hidden border bg-slate-100 shadow-sm flex flex-col justify-end p-2 transition-all"
                                         :class="p.isOversized ? 'border-red-500 ring-2 ring-red-400' : 'border-emerald-300 ring-1 ring-emerald-400'">
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
                                        <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 bg-emerald-600 text-[10px] font-bold text-white rounded shadow-sm">{{ __('New') }}</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-xs">
                        <p class="font-bold">{{ __('Maximum media limit reached (15/15).') }}</p>
                        <p class="text-slate-500 mt-0.5">{{ __('To upload new photos or videos, delete an existing media file above.') }}</p>
                    </div>
                @endif

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
                            $selectedAudiences = old('audience', $event->audience ?? []);
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
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Tags') }}</label>
                        <input name="tags" value="{{ old('tags', is_array($event->tags) ? implode(', ', $event->tags) : $event->tags) }}" class="input-field">
                        @error('tags')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Description') }}</label>
                        <textarea name="description" class="input-field" rows="5">{{ old('description', $event->getRawOriginal('description') ?? $event->description) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Save Toolbar --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('vendor.dashboard') }}" class="btn-secondary text-sm px-5 py-2.5">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn-primary text-sm px-6 py-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ __('Save Changes') }}
                </button>
            </div>
        </form>

        {{-- Keep media deletion forms outside the event update form. --}}
        @foreach($event->media as $m)
            <form id="delete-event-media-{{ $m->id }}" method="POST" action="{{ route('vendor.events.media.destroy', [$event->id, $m->id]) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        {{-- Ticket Categories & Pricing (Tiers) Section --}}
        <div class="surface p-6 sm:p-7 rounded-2xl shadow-sm border border-slate-200/80 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h2 class="text-base font-bold text-slate-950">{{ __('Ticket Categories & Pricing Tiers') }}</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Manage multiple paid ticket categories (Bronze, Silver, Gold, Diamond, or Custom), quantities, and availability.') }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    {{-- 1-Click Generate Default Tiers (Bronze, Silver, Gold, Diamond) --}}
                    <form method="POST" action="{{ route('vendor.tickets.generate_defaults', $event) }}" class="inline">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('{{ __('Generate default Bronze, Silver, Gold, and Diamond tiers for this event?') }}')"
                                class="btn-secondary text-xs py-2 px-3 flex items-center gap-1.5 rounded-xl text-slate-700 hover:text-emerald-700 hover:border-emerald-300 font-bold">
                            <span>✨ {{ __('Generate Default Tiers') }}</span>
                            <span class="text-[10px] text-slate-400 font-normal">({{ __('Bronze, Silver, Gold, Diamond') }})</span>
                        </button>
                    </form>

                    <a href="{{ route('vendor.tickets.create', $event) }}" class="btn-emerald text-xs py-2 px-3.5 flex items-center gap-1.5 rounded-xl font-bold shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('+ Add Ticket Tier') }}
                    </a>
                </div>
            </div>

            <div class="space-y-3.5">
                @forelse($event->ticketTypes as $ticket)
                    @php
                        $meta = $ticket->tierMeta();
                        $status = $ticket->availabilityStatus();
                    @endphp
                    <div class="p-4 sm:p-5 border border-slate-200 rounded-2xl bg-white hover:border-slate-300 transition-all shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                        {{-- Left: Tier Badge, Title, Description --}}
                        <div class="flex items-start gap-3.5 flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $meta['gradient'] }} flex items-center justify-center text-lg shrink-0 shadow-sm text-white">
                                {{ $meta['icon'] }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-slate-900 text-sm truncate">{{ $ticket->name }}</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $meta['bg_badge'] }}">
                                        {{ $meta['label'] }}
                                    </span>

                                    @if($ticket->is_active && $ticket->remainingQuantity() > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ __('Active & Available') }}
                                        </span>
                                    @elseif(!$ticket->is_active)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                            {{ __('Inactive') }}
                                        </span>
                                    @elseif($ticket->remainingQuantity() <= 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            {{ __('Sold Out') }}
                                        </span>
                                    @endif
                                </div>

                                @if($ticket->description)
                                    <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $ticket->description }}</p>
                                @endif

                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-[11px] text-slate-500">
                                    <span>
                                        {{ __('Allocated') }}: <strong class="text-slate-800">{{ $ticket->quantity }}</strong>
                                    </span>
                                    <span>•</span>
                                    <span>
                                        {{ __('Sold') }}: <strong class="text-slate-800">{{ $ticket->sold }}</strong>
                                    </span>
                                    <span>•</span>
                                    <span>
                                        {{ __('Available') }}: <strong class="{{ $ticket->remainingQuantity() > 0 ? 'text-emerald-700' : 'text-rose-600' }}">{{ $ticket->remainingQuantity() }}</strong>
                                    </span>
                                    @if($ticket->sales_start || $ticket->sales_end)
                                        <span>•</span>
                                        <span class="text-slate-400">
                                            📅 {{ $ticket->sales_start?->format('M j') ?? __('Immediate') }} &rarr; {{ $ticket->sales_end?->format('M j') ?? __('Event End') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Right: Price, Inventory Occupancy Bar, and Actions --}}
                        <div class="flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center gap-3 shrink-0 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100">
                            <div class="text-left md:text-right">
                                <span class="text-base font-black text-slate-950">
                                    {{ (float) $ticket->price <= 0 ? __('FREE') : '$'.number_format($ticket->price, 2).' '.($ticket->currency ?: 'USD') }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ __('per ticket') }}
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('vendor.tickets.edit', [$event, $ticket]) }}"
                                   class="btn-secondary text-xs py-1.5 px-3 rounded-lg flex items-center gap-1 font-semibold"
                                   title="{{ __('Edit Tier') }}">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    {{ __('Edit') }}
                                </a>

                                <form method="POST" action="{{ route('vendor.tickets.destroy', [$event, $ticket]) }}"
                                      onsubmit="return confirm('{{ $ticket->sold > 0 ? __('Deactivate this ticket tier?') : __('Delete this ticket tier?') }}');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs text-slate-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition-colors"
                                            title="{{ $ticket->sold > 0 ? __('Deactivate Tier') : __('Delete Tier') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 px-4 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50">
                        <div class="w-12 h-12 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-3">
                            🎟️
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">{{ __('No ticket categories configured yet') }}</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-4">
                            {{ __('Allow attendees to book tickets by adding custom tiers or generating default Bronze, Silver, Gold, and Diamond tiers.') }}
                        </p>
                        <div class="flex flex-wrap items-center justify-center gap-2">
                            <form method="POST" action="{{ route('vendor.tickets.generate_defaults', $event) }}">
                                @csrf
                                <button type="submit" class="btn-emerald text-xs py-2 px-4 shadow-sm font-bold rounded-xl">
                                    ✨ {{ __('Auto-Generate Default Tiers (Bronze, Silver, Gold, Diamond)') }}
                                </button>
                            </form>
                            <a href="{{ route('vendor.tickets.create', $event) }}" class="btn-secondary text-xs py-2 px-4 rounded-xl">
                                {{ __('+ Create Custom Ticket') }}
                            </a>
                        </div>
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
                            {{ __('Publish Event Live') }}
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('vendor.events.unpublish', $event) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-secondary text-xs py-2 px-4">
                            {{ __('Move to Draft') }}
                        </button>
                    </form>
                @endif
            </div>

            <form method="POST" action="{{ route('vendor.events.destroy', $event) }}" onsubmit="return confirm('{{ __('Permanently delete this event? This action cannot be undone.') }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded-lg transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    {{ __('Delete Event Permanently') }}
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
