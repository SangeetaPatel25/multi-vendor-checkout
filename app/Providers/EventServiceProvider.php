<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\PaymentSucceeded;
use App\Listeners\OrderPlacedListener;
use App\Listeners\PaymentSucceededListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderPlaced::class => [
            OrderPlacedListener::class,
        ],

        PaymentSucceeded::class => [
            PaymentSucceededListener::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
