<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('vendor.dashboard') }}" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="page-title">QR Code Check-In</h2>
                <p class="page-subtitle">Validate attendee tickets at the event gate.</p>
            </div>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content max-w-xl">
            <div class="surface p-8 text-center space-y-6">
                <div class="w-20 h-20 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-2xl flex items-center justify-center mx-auto text-4xl shadow-lg">
                    ✅
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-900">Scan & Validate</h3>
                    <p class="text-slate-600 mt-2 text-sm">Enter the ticket code from the attendee's QR ticket to mark them as checked in.</p>
                </div>

                @if (session('status'))
                    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">
                        <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <div>
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('vendor.checkin.validate') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2 text-left">Ticket Code</label>
                        <input type="text"
                            name="code"
                            class="input-field text-center text-lg font-mono tracking-widest"
                            placeholder="XXXXXXXXXXXXXXXX"
                            autocomplete="off"
                            autofocus
                            required>
                        <p class="text-xs text-slate-400 mt-1.5">Enter the code shown on the attendee's ticket or scan the QR code</p>
                    </div>
                    <button type="submit" class="btn-primary w-full text-base py-3.5">
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Validate Ticket
                    </button>
                </form>

                <div class="pt-4 border-t border-slate-100">
                    <p class="text-xs text-slate-400">Tip: Use a barcode scanner connected to your device for faster validation — it will auto-fill and submit the form.</p>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
