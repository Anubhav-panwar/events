<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Vendor Profile</h2>
            <p class="page-subtitle">Present your place, opening hours, socials, and media gallery.</p>
        </div>
    </x-slot>

    @php
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $existingHours = collect(old('opening_hours', $profile->opening_hours ?? []))->keyBy('day');
    @endphp

    <section class="page-section">
        <div class="app-content max-w-6xl space-y-6">
            @if (session('status'))
                <div class="surface p-4 text-emerald-700">{{ session('status') }}</div>
            @endif

            <div class="surface p-6">
                <form method="POST" action="{{ route('vendor.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Business Name</label>
                            <input name="business_name" value="{{ old('business_name', $profile->business_name ?? '') }}" class="input-field" required>
                            @error('business_name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Phone</label>
                            <input name="phone" value="{{ old('phone', $profile->phone ?? '') }}" class="input-field">
                            @error('phone')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Website</label>
                            <input name="website" value="{{ old('website', $profile->website ?? '') }}" class="input-field" placeholder="https://...">
                            @error('website')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Instagram</label>
                            <input name="instagram" value="{{ old('instagram', $profile->instagram ?? '') }}" class="input-field" placeholder="https://instagram.com/...">
                            @error('instagram')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Facebook</label>
                            <input name="facebook" value="{{ old('facebook', $profile->facebook ?? '') }}" class="input-field" placeholder="https://facebook.com/...">
                            @error('facebook')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">X / Twitter</label>
                            <input name="twitter" value="{{ old('twitter', $profile->twitter ?? '') }}" class="input-field" placeholder="https://x.com/...">
                            @error('twitter')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm text-slate-700 mb-1">Address</label>
                            <input name="address" value="{{ old('address', $profile->address ?? '') }}" class="input-field">
                            @error('address')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">City</label>
                            <input name="city" value="{{ old('city', $profile->city ?? '') }}" class="input-field">
                            @error('city')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Country</label>
                            <input name="country" value="{{ old('country', $profile->country ?? '') }}" class="input-field">
                            @error('country')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Latitude</label>
                            <input name="latitude" value="{{ old('latitude', $profile->latitude ?? '') }}" class="input-field">
                            @error('latitude')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 mb-1">Longitude</label>
                            <input name="longitude" value="{{ old('longitude', $profile->longitude ?? '') }}" class="input-field">
                            @error('longitude')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm text-slate-700 mb-1">Categories</label>
                            <select name="category_ids[]" multiple class="input-field h-36">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(in_array($cat->id, old('category_ids', $profile?->categories->pluck('id')->toArray() ?? [])))>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_ids')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm text-slate-700 mb-1">Description</label>
                            <textarea name="description" class="input-field" rows="5">{{ old('description', $profile->description ?? '') }}</textarea>
                            @error('description')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-3">Opening Hours</h3>
                        <div class="space-y-3">
                            @foreach($days as $index => $day)
                                @php
                                    $row = $existingHours->get($day, ['day' => $day, 'open' => '', 'close' => '', 'closed' => false]);
                                @endphp
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-center">
                                    <input type="hidden" name="opening_hours[{{ $index }}][day]" value="{{ $day }}">
                                    <div class="font-medium text-slate-700">{{ $day }}</div>
                                    <input type="time" name="opening_hours[{{ $index }}][open]" value="{{ $row['open'] ?? '' }}" class="input-field">
                                    <input type="time" name="opening_hours[{{ $index }}][close]" value="{{ $row['close'] ?? '' }}" class="input-field">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                        <input type="checkbox" name="opening_hours[{{ $index }}][closed]" value="1" @checked((bool)($row['closed'] ?? false))>
                                        Closed
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-700 mb-1">Upload Gallery Media</label>
                        <input type="file" name="media[]" multiple class="input-field">
                        <p class="text-xs text-slate-500 mt-1">Upload images or videos to present your place and activity.</p>
                    </div>

                    <div class="flex justify-end">
                        <button class="btn-primary">Save Profile</button>
                    </div>
                </form>
            </div>

            @if(($profile?->media?->count() ?? 0) > 0)
                <div class="surface p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-3">Current Gallery</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($profile->media as $m)
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
</x-app-layout>
