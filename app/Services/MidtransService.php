<?php

namespace App\Services;

use App\Models\Reservation;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Get parsed transaction status from Midtrans.
     *
     * @return array{transaction_status: string|null, payment_type: string}
     */
    public function getTransactionStatus(string $paymentReference): array
    {
        $response = Transaction::status($paymentReference);

        return [
            'transaction_status' => \is_array($response) ? ($response['transaction_status'] ?? null) : ($response->transaction_status ?? null),
            'payment_type' => \is_array($response) ? ($response['payment_type'] ?? 'unknown') : ($response->payment_type ?? 'unknown'),
        ];
    }

    /**
     * Build Snap payment parameters for a reservation.
     */
    public function buildSnapParams(Reservation $reservation, string $orderId): array
    {
        $package = $reservation->tourPackage;

        return [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $reservation->total_amount,
            ],
            'customer_details' => [
                'first_name' => $reservation->guest_name,
                'email' => $reservation->guest_email ?? 'walkin@example.com',
                'phone' => $reservation->user?->phone ?? '0000000000',
            ],
            'item_details' => [
                [
                    'id' => 'PKG-'.$package->id,
                    'price' => $package->price,
                    'quantity' => $reservation->party_size,
                    'name' => $package->name,
                ],
            ],
        ];
    }

    /**
     * Get Snap token for payment parameters.
     */
    public function getSnapToken(array $params): string
    {
        return Snap::getSnapToken($params);
    }
}
