@props(['align' => 'right', 'mobile' => false, 'variant' => 'default'])

@php
    $supportedLocales = [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'code' => 'EN',
            'flag' => '🇬🇧',
        ],
        'de' => [
            'name' => 'German',
            'native' => 'Deutsch',
            'code' => 'DE',
            'flag' => '🇩🇪',
        ],
        'it' => [
            'name' => 'Italian',
            'native' => 'Italiano',
            'code' => 'IT',
            'flag' => '🇮🇹',
        ],
    ];

    $currentLocale = app()->getLocale();
    if (!array_key_exists($currentLocale, $supportedLocales)) {
        $currentLocale = 'en';
    }
    $current = $supportedLocales[$currentLocale];

    $alignmentClasses = match ($align) {
        'left' => 'left-0 origin-top-left',
        'top' => 'bottom-full mb-2 right-0 origin-bottom-right',
        default => 'right-0 origin-top-right',
    };
@endphp

@if($mobile)
    {{-- Mobile In-Drawer Language Selector --}}
    <div x-data="{
            isChanging: false,
            selectLanguage(locale) {
                if (locale === '{{ $currentLocale }}' || this.isChanging) return;
                this.isChanging = true;
                try {
                    localStorage.setItem('locale', locale);
                    document.cookie = 'locale=' + locale + '; path=/; max-age=31536000; SameSite=Lax';
                } catch (e) {}

                window.location.href = '{{ url('/locale') }}/' + encodeURIComponent(locale);
            }
        }" class="w-full notranslate" translate="no">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2 px-1">
            {{ __('Language / Sprache / Lingua') }}
        </p>
        <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-100 rounded-xl border border-slate-200">
            @foreach($supportedLocales as $code => $data)
                <button type="button"
                        @click="selectLanguage('{{ $code }}')"
                        :disabled="isChanging"
                        class="flex flex-col items-center justify-center py-2 px-1 rounded-lg text-xs font-semibold transition-all notranslate {{ $currentLocale === $code ? 'bg-white text-emerald-800 shadow-sm border border-slate-200/80 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                    <span class="text-base leading-none mb-1">{{ $data['flag'] }}</span>
                    <span class="text-[11px]">{{ $data['native'] }}</span>
                </button>
            @endforeach
        </div>
    </div>
@else
    {{-- Desktop & Universal Top-Right Dropdown --}}
    <div x-data="{
            open: false,
            isChanging: false,
            selectLanguage(locale) {
                if (locale === '{{ $currentLocale }}' || this.isChanging) {
                    this.open = false;
                    return;
                }
                this.isChanging = true;
                this.open = false;

                try {
                    sessionStorage.setItem('scrollPos', window.scrollY);
                    localStorage.setItem('locale', locale);
                    document.cookie = 'locale=' + locale + '; path=/; max-age=31536000; SameSite=Lax';
                } catch (e) {}

                window.location.href = '{{ url('/locale') }}/' + encodeURIComponent(locale);
            }
        }"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        class="relative inline-block text-left z-30 notranslate"
        translate="no">

        {{-- Dropdown Trigger Button --}}
        <button type="button"
                @click="open = !open"
                :aria-expanded="open"
                aria-haspopup="true"
                aria-label="{{ __('Select Language') }}"
                title="{{ __('Select Language') }} ({{ $current['native'] }})"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs sm:text-sm font-semibold rounded-xl border transition-all shadow-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 notranslate {{ $variant === 'crm' ? 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-emerald-700 hover:border-slate-300' }}">
            <span class="text-base leading-none" aria-hidden="true">{{ $current['flag'] }}</span>
            <span class="hidden md:inline font-bold">{{ $current['native'] }}</span>
            <span class="md:hidden font-bold">{{ $current['code'] }}</span>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        {{-- Dropdown Menu Container --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute {{ $alignmentClasses }} mt-1.5 w-44 rounded-2xl bg-white shadow-xl ring-1 ring-slate-900/10 border border-slate-100 py-1.5 z-[100] focus:outline-none"
             style="display: none;"
             role="menu"
             aria-orientation="vertical">

            <div class="px-3 py-1.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                {{ __('Select Language') }}
            </div>

            <div class="py-1">
                @foreach($supportedLocales as $code => $data)
                    <button type="button"
                            @click="selectLanguage('{{ $code }}')"
                            role="menuitem"
                            class="w-full text-left flex items-center justify-between px-3.5 py-2 text-xs sm:text-sm font-medium transition-colors {{ $currentLocale === $code ? 'bg-emerald-50/80 text-emerald-800 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                        <span class="flex items-center gap-2.5">
                            <span class="text-lg leading-none" aria-hidden="true">{{ $data['flag'] }}</span>
                            <span>{{ $data['native'] }}</span>
                            <span class="text-[11px] text-slate-400 font-normal">({{ $data['code'] }})</span>
                        </span>
                        @if($currentLocale === $code)
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>
@endif
