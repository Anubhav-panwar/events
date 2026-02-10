<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Vendors</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($vendors as $vendor)
                    <div class="card p-4">
                        <div class="font-semibold">
                            <a class="text-blue-700" href="{{ route('vendors.show', $vendor->slug) }}">{{ $vendor->business_name }}</a>
                        </div>
                        <div class="text-sm text-gray-600">{{ $vendor->address }}</div>
                        <div class="mt-2 text-sm">{{ \Illuminate\Support\Str::limit($vendor->description, 120) }}</div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $vendors->links() }}</div>
        </div>
    </div>
</x-app-layout>
