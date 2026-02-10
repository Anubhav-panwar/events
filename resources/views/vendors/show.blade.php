<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ $vendor->business_name }}</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 card p-6">
                <div class="text-gray-600 mb-4">{{ $vendor->description }}</div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach(($vendor->events->flatMap->media->take(6)) as $m)
                        <img class="rounded" src="{{ Storage::disk($m->disk)->url($m->path) }}" alt="{{ $m->original_name }}">
                    @endforeach
                </div>
                <div class="mt-4">
                    <div class="font-semibold">Address</div>
                    <div>{{ $vendor->address }} {{ $vendor->city ? '• '.$vendor->city : '' }} {{ $vendor->country ? '• '.$vendor->country : '' }}</div>
                </div>
                @if($vendor->latitude && $vendor->longitude)
                <div class="mt-4">
                    <iframe class="w-full h-64 rounded" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        src="https://maps.google.com/maps?q={{ $vendor->latitude }},{{ $vendor->longitude }}&z=14&output=embed">
                    </iframe>
                </div>
                @endif
                <div class="mt-4 flex gap-3">
                    @if($vendor->website)<a class="text-blue-600" href="{{ $vendor->website }}" target="_blank">Website</a>@endif
                    @if($vendor->instagram)<a class="text-blue-600" href="{{ $vendor->instagram }}" target="_blank">Instagram</a>@endif
                    @if($vendor->facebook)<a class="text-blue-600" href="{{ $vendor->facebook }}" target="_blank">Facebook</a>@endif
                    @if($vendor->twitter)<a class="text-blue-600" href="{{ $vendor->twitter }}" target="_blank">Twitter</a>@endif
                </div>
            </div>
            <div class="card p-6">
                <div class="font-semibold">Followers</div>
                <div class="text-3xl">{{ $vendor->followers()->count() }}</div>
                @auth
                    <form method="POST" action="{{ route('vendors.follow', $vendor->slug) }}" class="mt-4">
                        @csrf
                        <button class="btn-primary">Follow Vendor</button>
                    </form>
                @endauth
            </div>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-8">
            <h3 class="font-semibold text-lg">Upcoming Events</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                @foreach($events as $event)
                    <x-event-card :event="$event" />
                @endforeach
            </div>
            <div class="mt-4">{{ $events->links() }}</div>
        </div>
    </div>
</x-app-layout>
