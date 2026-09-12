<x-crm-layout title="QR Check-In" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-800">Vendor CRM</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">QR Ticket Validation</span>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="border-b border-slate-200 pb-5">
            <h1 class="text-2xl font-bold tracking-tight text-slate-950">Attendee Ticket Check-In</h1>
            <p class="text-xs text-slate-500 mt-1">Scan attendee QR code passes or manually input ticket codes at the venue admission gate.</p>
        </div>

        {{-- Scanner / Input Box --}}
        <div class="surface p-8 text-center space-y-6">
            <div class="w-20 h-20 bg-gradient-to-br from-emerald-400 via-teal-500 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto text-white shadow-lg shadow-emerald-950/20">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>

            <div>
                <h2 class="text-xl font-bold text-slate-900">Scan or Enter Code</h2>
                <p class="text-slate-500 text-xs mt-1 max-w-sm mx-auto">Use a barcode / QR scanner device, or paste the unique ticket code from the customer's PDF or mobile ticket.</p>
            </div>

            @if (session('status'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 text-sm text-left shadow-sm">
                    <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center shrink-0 text-emerald-600 font-bold">
                        ✓
                    </div>
                    <div>
                        <p class="font-bold">Ticket Validated & Checked In</p>
                        <p class="text-xs text-emerald-700 mt-0.5">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('vendor.checkin.validate') }}" class="space-y-4 max-w-md mx-auto">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5 text-left">Unique Ticket Code</label>
                    <input type="text"
                           name="code"
                           class="input-field text-center text-xl font-mono tracking-widest uppercase font-bold py-3.5 focus:ring-emerald-500 focus:border-emerald-500"
                           placeholder="e.g. TC-9X42A..."
                           autocomplete="off"
                           autofocus
                           required>
                </div>
                <button type="submit" class="btn-emerald w-full py-3 text-sm font-bold shadow-md shadow-emerald-950/20 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Validate Admission
                </button>
            </form>

            <div class="pt-4 border-t border-slate-100 text-xs text-slate-400 flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Automated barcode / 2D QR scanners directly submit upon scan.</span>
            </div>
        </div>

    </div>
</x-crm-layout>
