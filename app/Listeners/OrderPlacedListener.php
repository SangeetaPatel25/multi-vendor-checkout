<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderPlacedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        $event->order->loadMissing('user', 'vendor');

        // Log order placement for analytics
        Log::info('Order placed', [
            'order_id' => $event->order->id,
            'user_id' => $event->order->user_id,
            'vendor_id' => $event->order->vendor_id,
            'total' => $event->order->total,
        ]);

        try {
            Mail::raw(
                "Your order #{$event->order->id} for vendor {$event->order->vendor->name} has been placed and is awaiting payment confirmation.",
                function ($message) use ($event) {
                    $message->to($event->order->user->email, $event->order->user->name)
                        ->subject("Order #{$event->order->id} placed");
                }
            );
        } catch (Throwable $exception) {
            Log::info('Mock order confirmation email', [
                'order_id' => $event->order->id,
                'recipient' => $event->order->user->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
