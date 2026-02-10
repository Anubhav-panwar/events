<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\VendorProfile;
use App\Policies\EventPolicy;
use App\Policies\VendorProfilePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Gate::define('admin', fn($user) => $user->isAdmin());
        Gate::define('vendor', fn($user) => $user->isVendor());

        Gate::policy(VendorProfile::class, VendorProfilePolicy::class);
        Gate::policy(Event::class, EventPolicy::class);
    }
}
