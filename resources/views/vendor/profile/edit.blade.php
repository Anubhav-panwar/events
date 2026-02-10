<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Vendor Profile</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="card sm:rounded">
                <div class="p-6">
                    @if (session('status'))
                        <div class="text-green-600 mb-4">{{ session('status') }}</div>
                    @endif
                    <form method="POST" action="{{ route('vendor.profile.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block">Business Name</label>
                                <input name="business_name" value="{{ old('business_name', $profile->business_name ?? '') }}" class="w-full border rounded p-2"/>
                                @error('business_name')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="block">Phone</label>
                                <input name="phone" value="{{ old('phone', $profile->phone ?? '') }}" class="w-full border rounded p-2"/>
                                @error('phone')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block">Website</label>
                                <input name="website" value="{{ old('website', $profile->website ?? '') }}" class="w-full border rounded p-2"/>
                                @error('website')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block">Address</label>
                                <input name="address" value="{{ old('address', $profile->address ?? '') }}" class="w-full border rounded p-2"/>
                                @error('address')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="block">Latitude</label>
                                <input name="latitude" value="{{ old('latitude', $profile->latitude ?? '') }}" class="w-full border rounded p-2"/>
                                @error('latitude')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="block">Longitude</label>
                                <input name="longitude" value="{{ old('longitude', $profile->longitude ?? '') }}" class="w-full border rounded p-2"/>
                                @error('longitude')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block">Categories</label>
                                <select name="category_ids[]" multiple class="w-full border rounded p-2">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected(in_array($cat->id, old('category_ids', $profile?->categories->pluck('id')->toArray() ?? [])))>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_ids')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block">Description</label>
                                <textarea name="description" class="w-full border rounded p-2" rows="4">{{ old('description', $profile->description ?? '') }}</textarea>
                                @error('description')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mt-4">
                            <button class="btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
