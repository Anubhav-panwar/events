<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Saved Events</h2>
            <p class="page-subtitle">Events you've bookmarked to attend later.</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content">
            @if($events->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($events as $event)
                        <x-event-card :event="$event" />
                    @endforeach
                </div>
                <div class="mt-6">{{ $events->links() }}</div>
            @else
                <div class="surface p-16 text-center max-w-lg mx-auto">
                    <div class="text-6xl mb-5">🔖</div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">No saved events yet</h3>
                    <p class="text-slate-600 mb-8">Browse events and click <strong>Save</strong> on any event to bookmark it here for easy access.</p>
                    <a href="{{ route('events.index') }}" class="btn-primary px-8 py-3">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Explore Events
                    </a>
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
