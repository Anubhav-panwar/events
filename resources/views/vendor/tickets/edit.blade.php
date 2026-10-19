<x-crm-layout :title="__('Edit Ticket Category')" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-800">{{ __('Vendor CRM') }}</a>
            <span>/</span>
            <a href="{{ route('vendor.dashboard') }}#events" class="hover:text-slate-800">{{ __('Events') }}</a>
            <span>/</span>
            <a href="{{ route('vendor.events.edit', $event) }}" class="hover:text-slate-800 truncate max-w-xs">{{ $event->title }}</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">{{ __('Edit Tier:') }} {{ $ticketType->name }}</span>
        </div>
    </x-slot>

    @php
        $meta = $ticketType->tierMeta();
    @endphp

    <div class="max-w-3xl mx-auto space-y-6"
         x-data="{
             selectedTier: '{{ old('tier', $ticketType->tier ?: 'custom') }}',
             name: '{{ old('name', $ticketType->getRawOriginal('name') ?: $ticketType->name) }}',
             price: '{{ old('price', number_format((float)$ticketType->price, 2, '.', '')) }}',
             quantity: '{{ old('quantity', $ticketType->quantity) }}',
             description: '{{ old('description', $ticketType->getRawOriginal('description') ?: $ticketType->description) }}',
             isActive: {{ old('is_active', $ticketType->is_active) ? 'true' : 'false' }},

             applyPreset(tier) {
                 this.selectedTier = tier;
                 if (tier === 'bronze' && !this.name) this.name = 'Bronze Admission';
                 if (tier === 'silver' && !this.name) this.name = 'Silver Pass';
                 if (tier === 'gold' && !this.name) this.name = 'Gold VIP';
                 if (tier === 'diamond' && !this.name) this.name = 'Diamond Elite';
             }
         }">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 pb-5">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-950">{{ __('Edit Ticket Category') }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $meta['bg_badge'] }}">
                        {{ $meta['icon'] }} {{ $meta['label'] }}
                    </span>
                    @if($ticketType->is_active)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ __('Active') }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                            {{ __('Inactive') }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ __('Update pricing, allocated seats, availability dates, and perks for') }} <strong class="text-slate-800">{{ $event->title }}</strong>.</p>
            </div>
            <a href="{{ route('vendor.events.edit', $event) }}" class="btn-secondary text-xs py-2 px-3.5 flex items-center gap-1.5 self-start sm:self-auto rounded-xl">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to Event') }}
            </a>
        </div>

        {{-- Sold Inventory Warning / Stats Banner --}}
        <div class="surface p-4 rounded-2xl border border-slate-200 flex items-center justify-between text-xs">
            <div class="flex items-center gap-4">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">{{ __('Tickets Sold') }}</span>
                    <span class="font-black text-slate-900 text-base">{{ $ticketType->sold }}</span>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">{{ __('Allocated Capacity') }}</span>
                    <span class="font-black text-slate-900 text-base">{{ $ticketType->quantity }}</span>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">{{ __('Remaining Seats') }}</span>
                    <span class="font-black text-emerald-700 text-base">{{ $ticketType->remainingQuantity() }}</span>
                </div>
            </div>
            @if($ticketType->sold > 0)
                <span class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1 rounded-xl font-medium">
                    ⚠️ {{ __('Has existing sales: keep quantity >= sold count') }}
                </span>
            @endif
        </div>

        {{-- Tier Selector Buttons --}}
        <div class="surface p-5 rounded-2xl border border-slate-200/80 space-y-2">
            <label class="block text-xs font-bold text-slate-700">{{ __('Ticket Tier Classification') }}</label>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                <button type="button" @click="applyPreset('bronze')"
                        :class="selectedTier === 'bronze' ? 'ring-2 ring-amber-500 bg-amber-50/80 border-amber-400' : 'bg-white hover:bg-slate-50 border-slate-200'"
                        class="p-2.5 rounded-xl border text-left transition-all">
                    <span class="text-sm">🥉</span>
                    <p class="text-xs font-bold text-amber-900 mt-0.5">{{ __('Bronze') }}</p>
                </button>
                <button type="button" @click="applyPreset('silver')"
                        :class="selectedTier === 'silver' ? 'ring-2 ring-slate-500 bg-slate-100 border-slate-400' : 'bg-white hover:bg-slate-50 border-slate-200'"
                        class="p-2.5 rounded-xl border text-left transition-all">
                    <span class="text-sm">🥈</span>
                    <p class="text-xs font-bold text-slate-800 mt-0.5">{{ __('Silver') }}</p>
                </button>
                <button type="button" @click="applyPreset('gold')"
                        :class="selectedTier === 'gold' ? 'ring-2 ring-yellow-500 bg-yellow-50/80 border-yellow-400' : 'bg-white hover:bg-slate-50 border-slate-200'"
                        class="p-2.5 rounded-xl border text-left transition-all">
                    <span class="text-sm">🥇</span>
                    <p class="text-xs font-bold text-yellow-900 mt-0.5">{{ __('Gold') }}</p>
                </button>
                <button type="button" @click="applyPreset('diamond')"
                        :class="selectedTier === 'diamond' ? 'ring-2 ring-cyan-500 bg-cyan-50/80 border-cyan-400' : 'bg-white hover:bg-slate-50 border-slate-200'"
                        class="p-2.5 rounded-xl border text-left transition-all">
                    <span class="text-sm">💎</span>
                    <p class="text-xs font-bold text-cyan-900 mt-0.5">{{ __('Diamond') }}</p>
                </button>
                <button type="button" @click="applyPreset('custom')"
                        :class="selectedTier === 'custom' ? 'ring-2 ring-emerald-500 bg-emerald-50/80 border-emerald-400' : 'bg-white hover:bg-slate-50 border-slate-200'"
                        class="p-2.5 rounded-xl border text-left transition-all col-span-2 sm:col-span-1">
                    <span class="text-sm">⭐</span>
                    <p class="text-xs font-bold text-slate-900 mt-0.5">{{ __('Custom') }}</p>
                </button>
            </div>
        </div>

        {{-- Form --}}
        <div class="surface p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200/80">
            <form method="POST" action="{{ route('vendor.tickets.update', [$event, $ticketType]) }}" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="tier" :value="selectedTier">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Name --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Ticket Category / Tier Name') }} <span class="text-red-500">*</span></label>
                        <input name="name" x-model="name" class="input-field" required>
                        @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Price --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Ticket Price') }} <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                            <input type="number" step="0.01" min="0" name="price" x-model="price" class="input-field pl-8" required>
                        </div>
                        @error('price')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Currency --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Currency') }}</label>
                        <select name="currency" class="input-field">
                            <option value="USD" @selected(old('currency', $ticketType->currency) === 'USD')>USD — US Dollar ($)</option>
                            <option value="EUR" @selected(old('currency', $ticketType->currency) === 'EUR')>EUR — Euro (€)</option>
                            <option value="GBP" @selected(old('currency', $ticketType->currency) === 'GBP')>GBP — British Pound (£)</option>
                            <option value="INR" @selected(old('currency', $ticketType->currency) === 'INR')>INR — Indian Rupee (₹)</option>
                        </select>
                    </div>

                    {{-- Quantity --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Allocated Quantity (Total Seats)') }} <span class="text-red-500">*</span></label>
                        <input type="number" min="{{ $ticketType->sold }}" name="quantity" x-model="quantity" class="input-field" required>
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('Must be at least :sold (already sold tickets).', ['sold' => $ticketType->sold]) }}</p>
                        @error('quantity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Description --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Category Description & Included Perks') }}</label>
                        <textarea name="description" x-model="description" rows="2" class="input-field" placeholder="{{ __('e.g. VIP seating, lounge pass, gift bag...') }}"></textarea>
                        @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Sales Start --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Sales Start Date & Time (Optional)') }}</label>
                        <input type="datetime-local" name="sales_start" value="{{ old('sales_start', $ticketType->sales_start?->format('Y-m-d\TH:i')) }}" class="input-field">
                    </div>

                    {{-- Sales End --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Sales End Date & Time (Optional)') }}</label>
                        <input type="datetime-local" name="sales_end" value="{{ old('sales_end', $ticketType->sales_end?->format('Y-m-d\TH:i')) }}" class="input-field">
                    </div>
                </div>

                {{-- Availability Toggle --}}
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

                <div class="flex items-center justify-between gap-3 pt-4 border-t border-slate-100">
                    <button type="button"
                            onclick="if(confirm('{{ __('Are you sure you want to delete or deactivate this ticket category?') }}')) document.getElementById('delete-ticket-form').submit();"
                            class="text-xs font-bold text-red-600 hover:text-red-800 py-2.5 px-3">
                        {{ $ticketType->sold > 0 ? __('Deactivate Category') : __('Delete Category') }}
                    </button>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('vendor.events.edit', $event) }}" class="btn-secondary text-xs py-2.5 px-4 rounded-xl">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn-emerald text-xs py-2.5 px-5 shadow-sm flex items-center gap-1.5 rounded-xl font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ __('Update Category') }}
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-ticket-form" method="POST" action="{{ route('vendor.tickets.destroy', [$event, $ticketType]) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>

    </div>
</x-crm-layout>
