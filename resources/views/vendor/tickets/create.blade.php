<x-crm-layout title="Add Ticket Type" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-800">Vendor CRM</a>
            <span>/</span>
            <a href="{{ route('vendor.dashboard') }}#events" class="hover:text-slate-800">Events</a>
            <span>/</span>
            <a href="{{ route('vendor.events.edit', $event) }}" class="hover:text-slate-800 truncate max-w-xs">{{ $event->title }}</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">New Ticket</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950">Add Ticket Type</h1>
                <p class="text-xs text-slate-500 mt-1">Configure pricing tier, quantity, and sales window for <strong class="text-slate-800">{{ $event->title }}</strong>.</p>
            </div>
            <a href="{{ route('vendor.events.edit', $event) }}" class="btn-secondary text-xs py-2 px-3.5 flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Event
            </a>
        </div>

        {{-- Ticket Form Card --}}
        <div class="surface p-6 sm:p-8">
            <form method="POST" action="{{ route('vendor.tickets.store', $event) }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Ticket Tier Name <span class="text-red-500">*</span></label>
                        <input name="name" value="{{ old('name') }}" class="input-field" placeholder="e.g. General Admission, VIP Pass, Early Bird" required>
                        @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Ticket Price <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', 0) }}" class="input-field pl-8" placeholder="0.00" required>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Set to 0.00 for free admission</p>
                        @error('price')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Currency</label>
                        <select name="currency" class="input-field">
                            <option value="USD" @selected(old('currency','USD') === 'USD')>USD — US Dollar ($)</option>
                            <option value="EUR" @selected(old('currency') === 'EUR')>EUR — Euro (€)</option>
                            <option value="GBP" @selected(old('currency') === 'GBP')>GBP — British Pound (£)</option>
                            <option value="INR" @selected(old('currency') === 'INR')>INR — Indian Rupee (₹)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Available Quantity (Seats) <span class="text-red-500">*</span></label>
                        <input type="number" min="1" name="quantity" value="{{ old('quantity', 100) }}" class="input-field" placeholder="e.g. 100" required>
                        @error('quantity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Sales Start Date &amp; Time (Optional)</label>
                        <input type="datetime-local" name="sales_start" value="{{ old('sales_start') }}" class="input-field">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Sales End Date &amp; Time (Optional)</label>
                        <input type="datetime-local" name="sales_end" value="{{ old('sales_end') }}" class="input-field">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('vendor.events.edit', $event) }}" class="btn-secondary text-xs py-2.5 px-4">
                        Cancel
                    </a>
                    <button type="submit" class="btn-emerald text-xs py-2.5 px-5 shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save Ticket Type
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-crm-layout>
