<x-crm-layout :title="__('Add Ticket Category')" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-800">{{ __('Vendor CRM') }}</a>
            <span>/</span>
            <a href="{{ route('vendor.dashboard') }}#events" class="hover:text-slate-800">{{ __('Events') }}</a>
            <span>/</span>
            <a href="{{ route('vendor.events.edit', $event) }}" class="hover:text-slate-800 truncate max-w-xs">{{ $event->title }}</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">{{ __('New Ticket Tier') }}</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6"
         x-data="{
             selectedTier: '{{ old('tier', 'bronze') }}',
             name: '{{ old('name', 'Bronze Admission') }}',
             price: '{{ old('price', '25.00') }}',
             quantity: '{{ old('quantity', '100') }}',
             description: '{{ old('description', 'Standard event admission, general area access and seating.') }}',
             isActive: {{ old('is_active', true) ? 'true' : 'false' }},

             applyPreset(tier) {
                 this.selectedTier = tier;
                 if (tier === 'bronze') {
                     this.name = 'Bronze Admission';
                     this.price = '25.00';
                     this.quantity = '100';
                     this.description = 'Standard event admission, general area access and seating.';
                 } else if (tier === 'silver') {
                     this.name = 'Silver Pass';
                     this.price = '50.00';
                     this.quantity = '75';
                     this.description = 'Preferred seating, express check-in lane, complimentary welcome beverage.';
                 } else if (tier === 'gold') {
                     this.name = 'Gold VIP';
                     this.price = '100.00';
                     this.quantity = '50';
                     this.description = 'VIP priority seating, dedicated lounge access, event gift bag & refreshments.';
                 } else if (tier === 'diamond') {
                     this.name = 'Diamond Elite';
                     this.price = '200.00';
                     this.quantity = '25';
                     this.description = 'Ultra-VIP all-access pass, front-row reserved seat, backstage meet & greet, exclusive hospitality.';
                 } else {
                     this.name = '';
                     this.price = '0.00';
                     this.quantity = '50';
                     this.description = '';
                 }
             }
         }">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950">{{ __('Add Ticket Category') }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Configure pricing tier, quantity, and availability for') }} <strong class="text-slate-800">{{ $event->title }}</strong>.</p>
            </div>
            <a href="{{ route('vendor.events.edit', $event) }}" class="btn-secondary text-xs py-2 px-3.5 flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to Event') }}
            </a>
        </div>

        {{-- Default Tiers Selector Pill Cards --}}
        <div class="surface p-5 rounded-2xl border border-slate-200/80 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ __('Choose a Default Tier Template') }}</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Select a preconfigured tier template to auto-fill details, or customize below.') }}</p>
                </div>
                <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                    {{ __('4 Standard Tiers Available') }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 pt-1">
                {{-- Bronze --}}
                <button type="button" @click="applyPreset('bronze')"
                        :class="selectedTier === 'bronze' ? 'ring-2 ring-amber-500 bg-amber-50/80 border-amber-400' : 'bg-white hover:bg-slate-50 border-slate-200'"
                        class="p-3 rounded-xl border text-left transition-all relative overflow-hidden group">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-base">🥉</span>
                        <span class="text-xs font-bold text-amber-900">{{ __('Bronze') }}</span>
                    </div>
                    <p class="text-xs font-extrabold text-slate-900">$25.00</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">100 {{ __('seats') }}</p>
                </button>

                {{-- Silver --}}
                <button type="button" @click="applyPreset('silver')"
                        :class="selectedTier === 'silver' ? 'ring-2 ring-slate-500 bg-slate-100 border-slate-400' : 'bg-white hover:bg-slate-50 border-slate-200'"
                        class="p-3 rounded-xl border text-left transition-all relative overflow-hidden group">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-base">🥈</span>
                        <span class="text-xs font-bold text-slate-800">{{ __('Silver') }}</span>
                    </div>
                    <p class="text-xs font-extrabold text-slate-900">$50.00</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">75 {{ __('seats') }}</p>
                </button>

                {{-- Gold --}}
                <button type="button" @click="applyPreset('gold')"
                        :class="selectedTier === 'gold' ? 'ring-2 ring-yellow-500 bg-yellow-50/80 border-yellow-400' : 'bg-white hover:bg-slate-50 border-slate-200'"
                        class="p-3 rounded-xl border text-left transition-all relative overflow-hidden group">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-base">🥇</span>
                        <span class="text-xs font-bold text-yellow-900">{{ __('Gold') }}</span>
                    </div>
                    <p class="text-xs font-extrabold text-slate-900">$100.00</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">50 {{ __('seats') }}</p>
                </button>

                {{-- Diamond --}}
                <button type="button" @click="applyPreset('diamond')"
                        :class="selectedTier === 'diamond' ? 'ring-2 ring-cyan-500 bg-cyan-50/80 border-cyan-400' : 'bg-white hover:bg-slate-50 border-slate-200'"
                        class="p-3 rounded-xl border text-left transition-all relative overflow-hidden group">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-base">💎</span>
                        <span class="text-xs font-bold text-cyan-900">{{ __('Diamond') }}</span>
                    </div>
                    <p class="text-xs font-extrabold text-slate-900">$200.00</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">25 {{ __('seats') }}</p>
                </button>

                {{-- Custom --}}
                <button type="button" @click="applyPreset('custom')"
                        :class="selectedTier === 'custom' ? 'ring-2 ring-emerald-500 bg-emerald-50/80 border-emerald-400' : 'bg-white hover:bg-slate-50 border-slate-200'"
                        class="p-3 rounded-xl border text-left transition-all relative overflow-hidden group col-span-2 sm:col-span-1">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-base">⭐</span>
                        <span class="text-xs font-bold text-slate-900">{{ __('Custom') }}</span>
                    </div>
                    <p class="text-xs font-extrabold text-slate-900">{{ __('Flexible') }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Custom setup') }}</p>
                </button>
            </div>
        </div>

        {{-- Ticket Form Card --}}
        <div class="surface p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200/80">
            <form method="POST" action="{{ route('vendor.tickets.store', $event) }}" class="space-y-6">
                @csrf
                <input type="hidden" name="tier" :value="selectedTier">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Tier Name --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Ticket Category / Tier Name') }} <span class="text-red-500">*</span></label>
                        <input name="name" x-model="name" class="input-field" placeholder="{{ __('e.g. Bronze Admission, Gold VIP, Early Bird') }}" required>
                        @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Ticket Price --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Ticket Price') }} <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                            <input type="number" step="0.01" min="0" name="price" x-model="price" class="input-field pl-8" placeholder="0.00" required>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('Set to 0.00 for complimentary tickets') }}</p>
                        @error('price')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Currency --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Currency') }}</label>
                        <select name="currency" class="input-field">
                            <option value="USD" @selected(old('currency','USD') === 'USD')>USD — US Dollar ($)</option>
                            <option value="EUR" @selected(old('currency') === 'EUR')>EUR — Euro (€)</option>
                            <option value="GBP" @selected(old('currency') === 'GBP')>GBP — British Pound (£)</option>
                            <option value="INR" @selected(old('currency') === 'INR')>INR — Indian Rupee (₹)</option>
                        </select>
                    </div>

                    {{-- Available Quantity --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Available Quantity (Allocated Seats)') }} <span class="text-red-500">*</span></label>
                        <input type="number" min="1" name="quantity" x-model="quantity" class="input-field" placeholder="e.g. 100" required>
                        @error('quantity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Tier Perks Description --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Category Description & Included Perks') }}</label>
                        <textarea name="description" x-model="description" rows="2" class="input-field" placeholder="{{ __('e.g. Priority front row seating, welcome drink, meet & greet access...') }}"></textarea>
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('Displayed to users during ticket booking so they know what is included in this tier.') }}</p>
                        @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Sales Start --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Sales Start Date & Time (Optional)') }}</label>
                        <input type="datetime-local" name="sales_start" value="{{ old('sales_start') }}" class="input-field">
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('Tickets become available for booking from this date.') }}</p>
                    </div>

                    {{-- Sales End --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Sales End Date & Time (Optional)') }}</label>
                        <input type="datetime-local" name="sales_end" value="{{ old('sales_end') }}" class="input-field">
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('Sales will automatically close after this date.') }}</p>
                    </div>
                </div>

                {{-- Active Availability Toggle --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <label class="text-xs font-bold text-slate-800">{{ __('Category Availability Status') }}</label>
                        <p class="text-[11px] text-slate-500">{{ __('When disabled, users cannot book this category even if seats remain.') }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="isActive" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('vendor.events.edit', $event) }}" class="btn-secondary text-xs py-2.5 px-4 rounded-xl">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn-emerald text-xs py-2.5 px-5 shadow-sm flex items-center gap-1.5 rounded-xl font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('Save Ticket Category') }}
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-crm-layout>
