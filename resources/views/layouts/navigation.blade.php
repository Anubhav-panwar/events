{{-- Customer-First Unified Navigation --}}
@php
    $currentUser = auth()->user();
    $isVendor = $currentUser?->isVendor() ?? false;
    $isAdmin = $currentUser?->isAdmin() ?? false;
    $hasAccountSavedRoute = \Illuminate\Support\Facades\Route::has('account.saved');
    $hasAccountTicketsRoute = \Illuminate\Support\Facades\Route::has('account.tickets.index');
    $hasVendorDashboardRoute = \Illuminate\Support\Facades\Route::has('vendor.dashboard');
    $hasAdminDashboardRoute = \Illuminate\Support\Facades\Route::has('admin.dashboard');
    $hasHomeRoute = \Illuminate\Support\Facades\Route::has('home');
    $hasAboutRoute = \Illuminate\Support\Facades\Route::has('about');
    $hasContactRoute = \Illuminate\Support\Facades\Route::has('contact');
    $hasEventsIndexRoute = \Illuminate\Support\Facades\Route::has('events.index');
    $hasVendorsIndexRoute = \Illuminate\Support\Facades\Route::has('vendors.index');
@endphp
<nav x-data="{ open: false }" class="bg-white border-b border-slate-200 fixed top-0 inset-x-0 z-50 transition-all duration-300">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- Logo & Brand --}}
            <div class="flex items-center">
                <div class="shrink-0 flex items-center">
                    <a href="{{ $hasHomeRoute ? route('home') : url('/') }}" class="flex items-center group">
                        <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-700 rounded-lg flex items-center justify-center mr-2.5 shadow-sm group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-slate-900 group-hover:text-emerald-700 transition-colors">Huddle</span>
                    </a>
                </div>

                {{-- Clean Customer-Centric Desktop Nav Links --}}
                <div class="hidden space-x-1 sm:ms-8 sm:flex items-center">
                    @if($hasHomeRoute)
                        <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                            Home
                        </x-nav-link>
                    @endif

                    @if($hasEventsIndexRoute)
                        <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.*') || request()->routeIs('search')">
                            Explore Events
                        </x-nav-link>
                    @endif

                    @if($hasVendorsIndexRoute)
                        <x-nav-link :href="route('vendors.index')" :active="request()->routeIs('vendors.*')">
                            Vendors
                        </x-nav-link>
                    @endif

                    @if($hasAboutRoute)
                        <x-nav-link :href="route('about')" :active="request()->routeIs('about')">
                            About
                        </x-nav-link>
                    @endif

                    @if($hasContactRoute)
                        <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">
                            Contact
                        </x-nav-link>
                    @endif

                    {{-- Customer-only personal links (Admins and Vendors manage these in their CRM) --}}
                    @auth
                        @if(!$isVendor && !$isAdmin)
                            @if($hasAccountSavedRoute)
                                <x-nav-link :href="route('account.saved')" :active="request()->routeIs('account.saved')">
                                    Saved
                                </x-nav-link>
                            @endif
                            @if($hasAccountTicketsRoute)
                                <x-nav-link :href="route('account.tickets.index')" :active="request()->routeIs('account.tickets.*')">
                                    My Tickets
                                </x-nav-link>
                            @endif
                        @endif
                    @endauth
                </div>
            </div>

            {{-- Right Side Actions --}}
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                @auth
                    {{-- Dedicated Single 'Go to Dashboard' Button for Admin and Vendor --}}
                    @if($isAdmin && $hasAdminDashboardRoute)
                        <a href="{{ route('admin.dashboard') }}"
                           class="btn-emerald text-xs py-2 px-3.5 inline-flex items-center gap-1.5 shadow-sm shadow-emerald-950/20 font-bold"
                           title="Super Admin Dashboard">
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                            <span>Admin Dashboard</span>
                            <span class="text-white/80">&rarr;</span>
                        </a>
                    @elseif($isVendor && $hasVendorDashboardRoute)
                        <a href="{{ route('vendor.dashboard') }}"
                           class="btn-emerald text-xs py-2 px-3.5 inline-flex items-center gap-1.5 shadow-sm shadow-emerald-950/20 font-bold"
                           title="Vendor Management Portal">
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                            <span>Go to Dashboard</span>
                            <span class="text-white/80">&rarr;</span>
                        </a>
                    @endif

                    {{-- User Account Dropdown --}}
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 px-3 py-1.5 border border-slate-200 text-sm font-bold rounded-xl text-slate-700 bg-white hover:bg-slate-50 focus:outline-none transition-all">
                                <div class="w-6 h-6 bg-slate-900 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            
                            <x-dropdown-link :href="route('profile.edit')">
                                My Profile
                            </x-dropdown-link>

                            @if($isAdmin && $hasAdminDashboardRoute)
                                <x-dropdown-link :href="route('admin.dashboard')" class="text-emerald-700 font-bold">
                                    Admin Dashboard &rarr;
                                </x-dropdown-link>
                            @elseif($isVendor && $hasVendorDashboardRoute)
                                <x-dropdown-link :href="route('vendor.dashboard')" class="text-emerald-700 font-bold">
                                    Vendor Dashboard &rarr;
                                </x-dropdown-link>
                            @else
                                @if($hasAccountSavedRoute)
                                    <x-dropdown-link :href="route('account.saved')">
                                        Saved Events
                                    </x-dropdown-link>
                                @endif
                                @if($hasAccountTicketsRoute)
                                    <x-dropdown-link :href="route('account.tickets.index')">
                                        My Tickets
                                    </x-dropdown-link>
                                @endif
                            @endif

                            <div class="border-t border-slate-100 mt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-slate-900 hover:text-red-700 font-bold">
                                        Sign Out
                                    </x-dropdown-link>
                                </form>
                            </div>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="px-3.5 py-2 text-sm font-bold text-slate-700 hover:text-emerald-700 transition-colors">Login</a>
                    <a href="{{ route('register') }}" class="btn-emerald text-sm py-2 px-4 shadow-sm">Register</a>
                @endauth
            </div>

            {{-- Mobile Hamburger Trigger --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Clean Customer-Centric Mobile Menu --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-slate-200 shadow-xl">
        <div class="pt-2 pb-3 space-y-1 px-4">
            @if($hasHomeRoute)
                <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-responsive-nav-link>
            @endif
            @if($hasEventsIndexRoute)
                <x-responsive-nav-link :href="route('events.index')" :active="request()->routeIs('events.*') || request()->routeIs('search')">Explore Events</x-responsive-nav-link>
            @endif
            @if($hasVendorsIndexRoute)
                <x-responsive-nav-link :href="route('vendors.index')" :active="request()->routeIs('vendors.*')">Vendors</x-responsive-nav-link>
            @endif
            @if($hasAboutRoute)
                <x-responsive-nav-link :href="route('about')" :active="request()->routeIs('about')">About</x-responsive-nav-link>
            @endif
            @if($hasContactRoute)
                <x-responsive-nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-responsive-nav-link>
            @endif

            @auth
                @if(!$isVendor && !$isAdmin)
                    <div class="border-t border-slate-100 pt-2 mt-2">
                        @if($hasAccountSavedRoute)
                            <x-responsive-nav-link :href="route('account.saved')" :active="request()->routeIs('account.saved')">Saved Events</x-responsive-nav-link>
                        @endif
                        @if($hasAccountTicketsRoute)
                            <x-responsive-nav-link :href="route('account.tickets.index')" :active="request()->routeIs('account.tickets.*')">My Tickets</x-responsive-nav-link>
                        @endif
                    </div>
                @else
                    {{-- Dedicated Mobile Single Dashboard CTA --}}
                    <div class="pt-2">
                        @if($isAdmin && $hasAdminDashboardRoute)
                            <a href="{{ route('admin.dashboard') }}" class="btn-emerald w-full py-2.5 text-center text-sm font-bold flex items-center justify-center gap-2">
                                <span>Admin Dashboard</span>
                                <span>&rarr;</span>
                            </a>
                        @elseif($isVendor && $hasVendorDashboardRoute)
                            <a href="{{ route('vendor.dashboard') }}" class="btn-emerald w-full py-2.5 text-center text-sm font-bold flex items-center justify-center gap-2">
                                <span>Go to Dashboard</span>
                                <span>&rarr;</span>
                            </a>
                        @endif
                    </div>
                @endif
            @endauth
        </div>

        <div class="pt-4 pb-4 border-t border-slate-200 bg-slate-50">
            @auth
                <div class="flex items-center gap-3 px-4 mb-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-900 flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 text-sm">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-slate-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                <div class="space-y-1 px-4">
                    <x-responsive-nav-link :href="route('profile.edit')">My Profile</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-700 font-bold">
                            Sign Out
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="flex gap-3 px-4">
                    <a href="{{ route('login') }}" class="btn-secondary flex-1 text-center py-2.5">Login</a>
                    <a href="{{ route('register') }}" class="btn-emerald flex-1 text-center py-2.5">Register</a>
                </div>
            @endauth
        </div>
    </div>
</nav>
