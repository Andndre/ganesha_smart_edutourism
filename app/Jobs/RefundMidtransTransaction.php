<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Transaction;

class RefundMidtransTransaction implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $orderId,
        public ?int $amount = null,
    ) {}

    public function handle(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        try {
            $params = [];
            if ($this->amount !== null) {
                $params['amount'] = $this->amount;
            }

            Transaction::refund($this->orderId, $params);

            Log::info('Midtrans transaction refunded', [
                'order_id' => $this->orderId,
                'amount' => $this->amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans refund failed', [
                'order_id' => $this->orderId,
                'amount' => $this->amount,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
