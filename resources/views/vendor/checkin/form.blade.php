<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">QR Check-In</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="card p-6">
                @if (session('status'))
                    <div class="text-green-600 mb-4">{{ session('status') }}</div>
                @endif
                <form method="POST" action="{{ route('vendor.checkin.validate') }}">
                    @csrf
                    <label class="block">Ticket Code</label>
                    <input name="code" class="w-full border rounded p-2" placeholder="Scan or paste code">
                    @error('code')<div class="text-red-600">{{ $message }}</div>@enderror
                    <div class="mt-4">
                        <button class="btn-primary">Validate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
