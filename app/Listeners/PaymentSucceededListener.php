<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PaymentSucceededListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentSucceeded $event): void
    {
        // Log payment success for financial records
        \Log::info('Payment succeeded', [
            'order_id' => $event->payment->order_id,
            'payment_id' => $event->payment->id,
            'amount' => $event->payment->amount,
            'status' => $event->payment->status,
        ]);

        // Here you could add:
        // - Send payment confirmation email
        // - Update financial dashboards
        // - Trigger fulfillment processes
        // - Send notifications to accounting
    }
}