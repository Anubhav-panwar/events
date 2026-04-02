<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('vendor.dashboard') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="page-title">Add Ticket Type</h2>
                <p class="page-subtitle">{{ $event->title }}</p>
            </div>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content max-w-3xl">
            <div class="surface p-8">
                <p class="text-sm text-slate-600 mb-6">Define a ticket type for this event. You can add multiple types (e.g. General, VIP, Early Bird) one at a time.</p>

                <form method="POST" action="{{ route('vendor.tickets.store', $event) }}" class="space-y-5">
                    @csrf

                    {{-- Name + Price --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Ticket Name <span class="text-red-500">*</span></label>
                            <input name="name" value="{{ old('name') }}" class="input-field" placeholder="e.g. General, VIP, Early Bird" required>
                            @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Price <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-medium">$</span>
                                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', 0) }}" class="input-field pl-7" placeholder="0.00" required>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Set to 0 for free tickets</p>
                            @error('price')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Currency + Quantity --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Currency</label>
                            <select name="currency" class="input-field">
                                <option value="USD" @selected(old('currency','USD') === 'USD')>USD — US Dollar</option>
                                <option value="EUR" @selected(old('currency') === 'EUR')>EUR — Euro</option>
                                <option value="GBP" @selected(old('currency') === 'GBP')>GBP — British Pound</option>
                                <option value="INR" @selected(old('currency') === 'INR')>INR — Indian Rupee</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Total Quantity <span class="text-red-500">*</span></label>
                            <input type="number" min="1" name="quantity" value="{{ old('quantity') }}" class="input-field" placeholder="e.g. 100" required>
                            @error('quantity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Sales Window --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Sales Window (optional)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-slate-500 mb-1 block">Sales Open</label>
                                <input type="datetime-local" name="sales_start" value="{{ old('sales_start') }}" class="input-field text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-slate-500 mb-1 block">Sales Close</label>
                                <input type="datetime-local" name="sales_end" value="{{ old('sales_end') }}" class="input-field text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="btn-primary">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Ticket Type
                        </button>
                        <a href="{{ route('vendor.dashboard') }}" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
