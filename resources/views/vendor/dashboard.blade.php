<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl">Vendor Dashboard</h2>
            <a href="{{ route('vendor.events.create') }}" class="btn-primary">New Event</a>
        </div>
    </x-slot>
    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="card p-6">
                @if(!$profile)
                    <div class="mb-4">
                        <a class="btn-primary" href="{{ route('vendor.profile.edit') }}">Create Your Vendor Profile</a>
                    </div>
                @else
                    <div class="mb-4">
                        <div class="font-semibold">{{ $profile->business_name }}</div>
                        <div class="text-gray-600">{{ $profile->address }}</div>
                    </div>
                @endif
                <h3 class="font-semibold text-lg mb-2">Your Events</h3>
                <div class="space-y-2">
                    @forelse($events ?? [] as $event)
                        <div class="flex justify-between border rounded p-2">
                            <div>
                                <a class="text-blue-600" href="{{ route('events.show', $event->slug) }}">{{ $event->title }}</a>
                                <div class="text-gray-600">{{ $event->event_date->format('Y-m-d') }} • {{ $event->status }}</div>
                            </div>
                            <a class="btn-secondary" href="{{ route('vendor.events.edit', $event) }}">Edit</a>
                        </div>
                    @empty
                        <div>No events yet.</div>
                    @endforelse
                </div>
                @if(($events ?? null) && method_exists($events, 'links'))
                    <div class="mt-4">{{ $events->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
