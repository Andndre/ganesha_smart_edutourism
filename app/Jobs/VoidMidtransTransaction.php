<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Transaction;

class VoidMidtransTransaction implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $orderId,
    ) {}

    public function handle(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        try {
            Transaction::cancel($this->orderId);

            Log::info('Midtrans transaction voided', [
                'order_id' => $this->orderId,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans void failed', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
