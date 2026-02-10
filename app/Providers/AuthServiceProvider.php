<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\VendorProfile;
use App\Policies\EventPolicy;
use App\Policies\VendorProfilePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        VendorProfile::class => VendorProfilePolicy::class,
        Event::class => EventPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('vendor', function ($user) {
            return $user->isVendor();
        });
    }
}
