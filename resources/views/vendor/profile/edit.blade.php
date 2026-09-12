<x-crm-layout title="Business Profile" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-800">Vendor CRM</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Business Profile</span>
        </div>
    </x-slot>

    @php
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $existingHours = collect(old('opening_hours', $profile->opening_hours ?? []))->keyBy('day');
        $hasVendorsShowRoute = \Illuminate\Support\Facades\Route::has('vendors.show');
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950">Vendor Business Profile</h1>
                <p class="text-xs text-slate-500 mt-1">Configure your public vendor storefront, gallery, operating hours, and location.</p>
            </div>
            @if($profile && $profile->slug && $hasVendorsShowRoute)
                <a href="{{ route('vendors.show', $profile->slug) }}" target="_blank" class="btn-secondary text-xs py-2 px-3.5 flex items-center gap-1.5 self-start sm:self-auto">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Preview Public Profile &rarr;
                </a>
            @endif
        </div>

        <form method="POST" action="{{ route('vendor.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Card 1: Core Business Details --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        1. Business Overview
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Business / Brand Name <span class="text-red-500">*</span></label>
                        <input name="business_name" value="{{ old('business_name', $profile->business_name ?? '') }}" class="input-field" placeholder="e.g. Acme Experiences & Events" required>
                        @error('business_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Select Categories</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 border border-slate-200 rounded-xl p-3 bg-slate-50/50 max-h-48 overflow-y-auto">
                            @foreach($categories as $cat)
                                @php
                                    $checked = in_array($cat->id, old('category_ids', $profile?->categories->pluck('id')->toArray() ?? []));
                                @endphp
                                <label class="flex items-center gap-2 text-xs text-slate-700 p-1.5 hover:bg-white rounded-lg cursor-pointer transition-colors">
                                    <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" @checked($checked) class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span>{{ $cat->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('category_ids')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">About / Bio</label>
                        <textarea name="description" class="input-field" rows="4" placeholder="Tell attendees about your organisation, background, experience, and what makes your events unique...">{{ old('description', $profile->description ?? '') }}</textarea>
                        @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Card 2: Contact & Socials --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        2. Contact &amp; Social Media Links
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Official Phone Number</label>
                        <input name="phone" value="{{ old('phone', $profile->phone ?? '') }}" class="input-field" placeholder="+1 (555) 000-0000">
                        @error('phone')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Official Website</label>
                        <input name="website" value="{{ old('website', $profile->website ?? '') }}" class="input-field" placeholder="https://yourwebsite.com">
                        @error('website')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Instagram URL</label>
                        <input name="instagram" value="{{ old('instagram', $profile->instagram ?? '') }}" class="input-field" placeholder="https://instagram.com/yourhandle">
                        @error('instagram')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Facebook Page</label>
                        <input name="facebook" value="{{ old('facebook', $profile->facebook ?? '') }}" class="input-field" placeholder="https://facebook.com/yourpage">
                        @error('facebook')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">X / Twitter</label>
                        <input name="twitter" value="{{ old('twitter', $profile->twitter ?? '') }}" class="input-field" placeholder="https://x.com/yourhandle">
                        @error('twitter')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Card 3: Location --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        3. Business Physical Address
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Physical Street Address</label>
                        <input name="address" value="{{ old('address', $profile->address ?? '') }}" class="input-field" placeholder="100 Broadway, Suite 400">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
                        <input name="city" value="{{ old('city', $profile->city ?? '') }}" class="input-field" placeholder="New York">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Country</label>
                        <input name="country" value="{{ old('country', $profile->country ?? '') }}" class="input-field" placeholder="USA">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Latitude</label>
                        <input name="latitude" value="{{ old('latitude', $profile->latitude ?? '') }}" class="input-field" placeholder="40.7128">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Longitude</label>
                        <input name="longitude" value="{{ old('longitude', $profile->longitude ?? '') }}" class="input-field" placeholder="-74.0060">
                    </div>
                </div>
            </div>

            {{-- Card 4: Operating Hours --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        4. Opening Hours Schedule
                    </h2>
                </div>

                <div class="space-y-2">
                    @foreach($days as $index => $day)
                        @php
                            $row = $existingHours->get($day, ['day' => $day, 'open' => '', 'close' => '', 'closed' => false]);
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-center p-2.5 rounded-lg border border-slate-100 bg-slate-50/50">
                            <input type="hidden" name="opening_hours[{{ $index }}][day]" value="{{ $day }}">
                            <div class="text-xs font-bold text-slate-800">{{ $day }}</div>
                            <input type="time" name="opening_hours[{{ $index }}][open]" value="{{ $row['open'] ?? '' }}" class="input-field text-xs py-1.5">
                            <input type="time" name="opening_hours[{{ $index }}][close]" value="{{ $row['close'] ?? '' }}" class="input-field text-xs py-1.5">
                            <label class="inline-flex items-center gap-1.5 text-xs text-slate-600 cursor-pointer">
                                <input type="checkbox" name="opening_hours[{{ $index }}][closed]" value="1" @checked((bool)($row['closed'] ?? false)) class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                <span>Closed on this day</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Card 5: Gallery Media --}}
            <div class="surface p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        5. Vendor Profile Media Gallery
                    </h2>
                </div>

                @if(($profile?->media?->count() ?? 0) > 0)
                    <div>
                        <p class="text-xs font-bold text-slate-700 mb-2">Current Media:</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($profile->media as $m)
                                <div class="aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shadow-sm">
                                    @if($m->type === 'video')
                                        <video class="w-full h-full object-cover" controls src="{{ $m->url }}"></video>
                                    @else
                                        <img src="{{ $m->url }}" class="w-full h-full object-cover" alt="{{ $m->original_name }}">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Upload Additional Photos / Videos</label>
                    <input type="file" name="media[]" multiple class="input-field" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm">
                    <p class="text-[11px] text-slate-500 mt-1">Upload images or videos presenting your venue, amenities, or past events.</p>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('vendor.dashboard') }}" class="btn-secondary text-sm px-5 py-2.5">
                    Cancel
                </a>
                <button type="submit" class="btn-primary text-sm px-6 py-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Business Profile
                </button>
            </div>
        </form>

    </div>
</x-crm-layout>
