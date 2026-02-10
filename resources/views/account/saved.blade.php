<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Saved Events</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($events as $event)
                    <x-event-card :event="$event" />
                @endforeach
            </div>
            <div class="mt-4">{{ $events->links() }}</div>
        </div>
    </div>
</x-app-layout>
