<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">{{ $vendor->business_name }}</h2>
            <p class="page-subtitle">Vendor profile, media highlights, and upcoming events.</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 surface p-6">
                <div class="text-slate-600 mb-4">{{ $vendor->description }}</div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach(($vendor->events->flatMap->media->take(6)) as $m)
                        <img class="rounded-xl border border-slate-200" src="{{ Storage::disk($m->disk)->url($m->path) }}" alt="{{ $m->original_name }}">
                    @endforeach
                </div>
                <div class="mt-5">
                    <div class="font-semibold text-slate-900">Address</div>
                    <div class="text-slate-700">{{ $vendor->address }} {{ $vendor->city ? '• '.$vendor->city : '' }} {{ $vendor->country ? '• '.$vendor->country : '' }}</div>
                </div>
                @if($vendor->latitude && $vendor->longitude)
                <div class="mt-5">
                    <iframe class="w-full h-64 rounded-xl border border-slate-200" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        src="https://maps.google.com/maps?q={{ $vendor->latitude }},{{ $vendor->longitude }}&z=14&output=embed">
                    </iframe>
                </div>
                @endif
                <div class="mt-5 flex flex-wrap gap-3">
                    @if($vendor->website)<a class="text-blue-600" href="{{ $vendor->website }}" target="_blank">Website</a>@endif
                    @if($vendor->instagram)<a class="text-blue-600" href="{{ $vendor->instagram }}" target="_blank">Instagram</a>@endif
                    @if($vendor->facebook)<a class="text-blue-600" href="{{ $vendor->facebook }}" target="_blank">Facebook</a>@endif
                    @if($vendor->twitter)<a class="text-blue-600" href="{{ $vendor->twitter }}" target="_blank">Twitter</a>@endif
                </div>
            </div>
            <div class="surface p-6">
                <div class="font-semibold text-slate-900">Followers</div>
                <div class="text-3xl text-slate-900 mt-2">{{ $vendor->followers()->count() }}</div>
                @auth
                    <form method="POST" action="{{ route('vendors.follow', $vendor->slug) }}" class="mt-4">
                        @csrf
                        <button class="btn-primary">Follow Vendor</button>
                    </form>
                @endauth
            </div>
        </div>

        <div class="app-content mt-8">
            <h3 class="text-xl font-semibold text-slate-900">Upcoming Events</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                @forelse($events as $event)
                    <x-event-card :event="$event" />
                @empty
                    <div class="md:col-span-3 surface p-8 text-center text-slate-600">No upcoming events from this vendor yet.</div>
                @endforelse
            </div>
            <div class="mt-4">{{ $events->links() }}</div>
        </div>
    </section>
</x-app-layout>
