<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Vendors</h2>
            <p class="page-subtitle">Connect with trusted organizers and discover quality event partners.</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @forelse($vendors as $vendor)
                    <div class="surface p-5">
                        <div class="font-semibold text-lg">
                            <a class="text-emerald-700 hover:text-emerald-800" href="{{ route('vendors.show', $vendor->slug) }}">{{ $vendor->business_name }}</a>
                        </div>
                        <div class="text-sm text-slate-600 mt-2">{{ $vendor->address }}</div>
                        <div class="mt-3 text-sm text-slate-700">{{ \Illuminate\Support\Str::limit($vendor->description, 120) }}</div>
                    </div>
                @empty
                    <div class="md:col-span-3 surface p-8 text-center text-slate-600">No vendors available right now.</div>
                @endforelse
            </div>
            <div>{{ $vendors->links() }}</div>
        </div>
    </section>
</x-app-layout>
