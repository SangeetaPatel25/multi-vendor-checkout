<?php

namespace App\Console\Commands;

use App\Services\CheckoutService;
use Illuminate\Console\Command;

class CancelUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-unpaid {--minutes=30 : Cancel orders older than this many minutes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel stale unpaid orders and restore reserved inventory';

    /**
     * Execute the console command.
     */
    public function handle(CheckoutService $checkoutService): int
    {
        $minutes = (int) $this->option('minutes');
        $cancelledCount = $checkoutService->cancelExpiredPendingOrders($minutes);

        $this->info("Cancelled {$cancelledCount} unpaid order(s).");

        return self::SUCCESS;
    }
}
