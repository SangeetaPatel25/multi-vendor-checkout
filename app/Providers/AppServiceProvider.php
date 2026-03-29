<?php

namespace App\Providers;

use App\Policies\AdminPolicy;
use Illuminate\Support\Facades\Gate;
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
        // Register Gates for admin access
        Gate::define('admin.access', [AdminPolicy::class, 'accessAdmin']);
        Gate::define('admin.view-orders', [AdminPolicy::class, 'viewOrders']);
        Gate::define('admin.view-order', [AdminPolicy::class, 'viewOrder']);
        Gate::define('admin.view-vendors', [AdminPolicy::class, 'viewVendors']);
        Gate::define('admin.view-customers', [AdminPolicy::class, 'viewCustomers']);
        Gate::define('admin.view-stats', [AdminPolicy::class, 'viewStats']);
    }
}
