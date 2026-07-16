{{-- Role-aware Navigation: Guest / User / Vendor / Admin --}}
@php
    $currentUser = auth()->user();
    $isVendor = $currentUser?->isVendor() ?? false;
    $isAdmin = $currentUser?->isAdmin() ?? false;
    $hasAccountSavedRoute = \Illuminate\Support\Facades\Route::has('account.saved');
    $hasAccountTicketsRoute = \Illuminate\Support\Facades\Route::has('account.tickets.index');
@endphp
<nav x-data="{ open: false }" class="bg-white border-b border-slate-200 fixed top-0 inset-x-0 z-50 transition-all duration-300">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- Logo --}}
            <div class="flex items-center">
                <div class="shrink-0 flex items-center">
                    <a href="{{ \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/') }}" class="flex items-center group">
                        <div class="w-8 h-8 bg-slate-900 rounded flex items-center justify-center mr-2 shadow-sm">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-slate-900">Huddle</span>
                    </a>
                </div>

                {{-- Desktop Nav Links --}}
                <div class="hidden space-x-1 sm:ms-8 sm:flex items-center">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        Home
                    </x-nav-link>
                    <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.*') || request()->routeIs('search')">
                        Explore
                    </x-nav-link>
                    <x-nav-link :href="route('vendors.index')" :active="request()->routeIs('vendors.*')">
                        Vendors
                    </x-nav-link>

                    @auth
                        {{-- User-only links --}}
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

                        {{-- Vendor links --}}
                        @if($isVendor)
                            <x-nav-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.dashboard')">
                                Dashboard
                            </x-nav-link>
                            <x-nav-link :href="route('vendor.events.create')" :active="request()->routeIs('vendor.events.create')">
                                New Event
                            </x-nav-link>
                            <x-nav-link :href="route('vendor.orders.index')" :active="request()->routeIs('vendor.orders.*')">
                                Orders
                            </x-nav-link>
                            <x-nav-link :href="route('vendor.checkin.form')" :active="request()->routeIs('vendor.checkin.*')">
                                Check-In
                            </x-nav-link>
                        @endif

                        {{-- Admin links --}}
                        @if($isAdmin)
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                                Admin
                            </x-nav-link>
                        @endif
                    @endauth

                    <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-nav-link>
                </div>
            </div>

            {{-- Right side --}}
            <div class="hidden sm:flex sm:items-center sm:gap-4">
                @auth
                    {{-- Role badge --}}
                    @if($isVendor)
                        <span class="badge-emerald">Vendor</span>
                    @elseif($isAdmin)
                        <span class="badge">Admin</span>
                    @endif

                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2.5 px-3 py-1.5 border border-slate-200 text-sm font-bold rounded text-slate-700 bg-white hover:bg-slate-50 focus:outline-none transition-all">
                                <div class="w-6 h-6 bg-slate-900 rounded-sm flex items-center justify-center text-white text-xs font-bold">
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
                            @if($isVendor)
                                <x-dropdown-link :href="route('vendor.profile.edit')">
                                    Vendor Profile
                                </x-dropdown-link>
                            @elseif($isAdmin)
                                <x-dropdown-link :href="route('admin.dashboard')">
                                    Admin Dashboard
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
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-slate-900 transition-colors">Login</a>
                    <a href="{{ route('register') }}" class="btn-primary">Register</a>
                @endauth
            </div>

            {{-- Mobile Hamburger --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-slate-200">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('events.index')" :active="request()->routeIs('events.*') || request()->routeIs('search')">Explore Events</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('vendors.index')" :active="request()->routeIs('vendors.index')">Vendors</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-responsive-nav-link>

            @auth
                <div class="border-t border-slate-100 pt-2 mt-2">
                    @if(!$isVendor && !$isAdmin)
                        @if($hasAccountSavedRoute)
                            <x-responsive-nav-link :href="route('account.saved')" :active="request()->routeIs('account.saved')">Saved Events</x-responsive-nav-link>
                        @endif
                        @if($hasAccountTicketsRoute)
                            <x-responsive-nav-link :href="route('account.tickets.index')" :active="request()->routeIs('account.tickets.*')">My Tickets</x-responsive-nav-link>
                        @endif
                    @endif
                    @if($isVendor)
                        <x-responsive-nav-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.dashboard')">Dashboard</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('vendor.events.create')">New Event</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('vendor.orders.index')" :active="request()->routeIs('vendor.orders.*')">Orders</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('vendor.checkin.form')" :active="request()->routeIs('vendor.checkin.*')">QR Check-In</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('vendor.profile.edit')">Vendor Profile</x-responsive-nav-link>
                    @endif
                    @if($isAdmin)
                        <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">Admin Panel</x-responsive-nav-link>
                    @endif
                </div>
            @endauth
        </div>

        <div class="pt-4 pb-3 border-t border-slate-200">
            @auth
                <div class="flex items-center gap-3 px-4 mb-3">
                    <div class="w-10 h-10 rounded bg-slate-900 flex items-center justify-center text-white font-bold">
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
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Sign Out</x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="flex gap-3 px-4">
                    <a href="{{ route('login') }}" class="btn-secondary flex-1">Login</a>
                    <a href="{{ route('register') }}" class="btn-primary flex-1">Register</a>
                </div>
            @endauth
        </div>
    </div>
</nav>
