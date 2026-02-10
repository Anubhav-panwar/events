<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Create Event</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="card sm:rounded">
                <div class="p-6">
                    <form method="POST" action="{{ route('vendor.events.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block">Title</label>
                                <input name="title" value="{{ old('title') }}" class="w-full border rounded p-2"/>
                                @error('title')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="block">Status</label>
                                <select name="status" class="w-full border rounded p-2">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                                @error('status')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="block">Date</label>
                                <input type="date" name="event_date" value="{{ old('event_date') }}" class="w-full border rounded p-2"/>
                                @error('event_date')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="block">Start Time</label>
                                <input type="time" name="start_time" value="{{ old('start_time') }}" class="w-full border rounded p-2"/>
                                @error('start_time')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="block">End Time</label>
                                <input type="time" name="end_time" value="{{ old('end_time') }}" class="w-full border rounded p-2"/>
                                @error('end_time')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block">Address</label>
                                <input name="address" value="{{ old('address') }}" class="w-full border rounded p-2"/>
                                @error('address')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="block">Latitude</label>
                                <input name="latitude" value="{{ old('latitude') }}" class="w-full border rounded p-2"/>
                                @error('latitude')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="block">Longitude</label>
                                <input name="longitude" value="{{ old('longitude') }}" class="w-full border rounded p-2"/>
                                @error('longitude')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block">Description</label>
                                <textarea name="description" class="w-full border rounded p-2" rows="4">{{ old('description') }}</textarea>
                                @error('description')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block">Media (images/videos)</label>
                                <input type="file" name="media[]" multiple class="w-full border rounded p-2"/>
                                @error('media')<div class="text-red-600">{{ $message }}</div>@enderror
                                @error('media.*')<div class="text-red-600">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mt-4">
                            <button class="btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
