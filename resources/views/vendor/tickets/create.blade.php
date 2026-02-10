<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Add Ticket Type: {{ $event->title }}</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card p-6">
                <form method="POST" action="{{ route('vendor.tickets.store', $event) }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block">Name</label>
                            <input name="name" class="w-full border rounded p-2" required>
                        </div>
                        <div>
                            <label class="block">Price</label>
                            <input type="number" step="0.01" name="price" class="w-full border rounded p-2" required>
                        </div>
                        <div>
                            <label class="block">Currency</label>
                            <input name="currency" value="USD" class="w-full border rounded p-2" required>
                        </div>
                        <div>
                            <label class="block">Quantity</label>
                            <input type="number" name="quantity" class="w-full border rounded p-2" required>
                        </div>
                        <div>
                            <label class="block">Sales Start</label>
                            <input type="datetime-local" name="sales_start" class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block">Sales End</label>
                            <input type="datetime-local" name="sales_end" class="w-full border rounded p-2">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button class="btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
