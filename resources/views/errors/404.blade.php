<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">{{ __('Page Not Found') }}</h2>
            <p class="page-subtitle">{{ __('The page you are looking for does not exist or has been moved.') }}</p>
        </div>
    </x-slot>

    <div class="min-h-[60vh] flex items-center justify-center py-16 px-4">
        <div class="max-w-md w-full text-center">
            <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-3xl mx-auto flex items-center justify-center font-bold text-3xl mb-6 shadow-inner">
                404
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 mb-3 tracking-tight">
                {{ __('Oops! Page not found') }}
            </h1>
            <p class="text-slate-600 text-sm leading-relaxed mb-8">
                {{ __('We could not find the page you were looking for. It might have been deleted, moved, or never existed in the first place.') }}
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('home') }}" class="btn-emerald w-full sm:w-auto px-6 py-2.5 text-sm font-bold shadow-md">
                    {{ __('Back to Homepage') }}
                </a>
                <a href="{{ route('events.index') }}" class="btn-secondary w-full sm:w-auto px-6 py-2.5 text-sm font-bold">
                    {{ __('Explore Events') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
