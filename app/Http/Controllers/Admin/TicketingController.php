<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\TourPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class TicketingController extends Controller
{
    public function index()
    {
        // Auto-sync pending walk-in QRIS reservations directly with Midtrans
        $pendingWalkIns = Reservation::where('status', 'pending')
            ->where('payment_method', 'qris')
            ->whereNotNull('payment_reference')
            ->whereDate('scheduled_date', today())
            ->get();

        if ($pendingWalkIns->count() > 0) {
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');

            foreach ($pendingWalkIns as $reservation) {
                try {
                    $statusResponse = Transaction::status($reservation->payment_reference);

                    $transactionStatus = \is_array($statusResponse) ? ($statusResponse['transaction_status'] ?? null) : ($statusResponse->transaction_status ?? null);
                    $paymentType = \is_array($statusResponse) ? ($statusResponse['payment_type'] ?? 'unknown') : ($statusResponse->payment_type ?? 'unknown');

                    if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                        $reservation->payment_status = 'paid';
                        $reservation->status = 'completed';
                        $reservation->payment_method = $paymentType;
                        $reservation->save();
                    } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                        $reservation->payment_status = 'unpaid';
                        $reservation->status = 'cancelled';
                        $reservation->save();
                    }
                } catch (\Exception $e) {
                    // Ignored (Midtrans throws Exception if transaction doesn't exist yet)
                }
            }
        }

        $packages = TourPackage::all();
        $reservations = Reservation::with(['user', 'tourPackage'])
            ->whereDate('scheduled_date', today())
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate statistics for confirmed and completed tickets today
        $todayPaidReservations = $reservations->whereIn('status', ['confirmed', 'completed']);
        $totalTicketsSold = $todayPaidReservations->sum('party_size');
        $totalRevenue = $todayPaidReservations->sum('total_amount');
        $cashRevenue = $todayPaidReservations->where('payment_method', 'cash')->sum('total_amount');
        $qrisRevenue = $todayPaidReservations->where('payment_method', '!=', 'cash')->sum('total_amount');

        $reservationsList = $reservations->map(function ($res) {
            return [
                'id' => $res->id,
                'guest_name' => $res->user ? $res->user->name : $res->guest_name,
                'is_walkin' => ! $res->user,
                'package_name' => $res->tourPackage->name ?? 'N/A',
                'party_size' => $res->party_size,
                'total_amount' => $res->total_amount,
                'status' => $res->status,
                'payment_method' => $res->payment_method,
                'time' => $res->created_at->format('H:i'),
                'timestamp' => $res->created_at->timestamp,
            ];
        })->values();

        return view('staff.ticketing.index', compact(
            'packages',
            'reservations',
            'reservationsList',
            'totalTicketsSold',
            'totalRevenue',
            'cashRevenue',
            'qrisRevenue'
        ));
    }

    public function storeWalkIn(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'party_size' => 'required|integer|min:1',
            'tour_package_id' => 'required|exists:tour_packages,id',
            'payment_method' => 'required|in:cash,qris',
        ]);

        $package = TourPackage::findOrFail($validated['tour_package_id']);

        $reservation = new Reservation;
        $reservation->fill($validated);
        $reservation->reservation_type = 'package';
        $reservation->scheduled_date = today();
        $reservation->scheduled_time = now()->format('H:i:00');
        $reservation->total_amount = $package->price * $validated['party_size'];
        $reservation->qr_code = 'WALKIN-'.strtoupper(Str::random(10));

        if ($validated['payment_method'] === 'cash') {
            $reservation->status = 'completed';
            $reservation->payment_status = 'paid';
            $reservation->payment_method = 'cash';
            $reservation->save();

            return response()->json([
                'success' => true,
                'payment_method' => 'cash',
                'message' => 'Tiket Walk-in (Tunai) berhasil dibuat!',
            ]);
        } else {
            $orderId = 'WALKIN-'.strtoupper(Str::random(8)).'-'.time();
            $reservation->status = 'pending';
            $reservation->payment_status = 'unpaid';
            $reservation->payment_method = 'qris';
            $reservation->payment_reference = $orderId;
            $reservation->save();

            // Set Midtrans configuration
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = config('midtrans.is_sanitized');
            Config::$is3ds = config('midtrans.is_3ds');

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $reservation->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $reservation->guest_name,
                    'email' => $reservation->guest_email ?? 'walkin@example.com',
                    'phone' => $reservation->guest_phone ?? '0000000000',
                ],
                'item_details' => [
                    [
                        'id' => 'PKG-'.$package->id,
                        'price' => $package->price,
                        'quantity' => $validated['party_size'],
                        'name' => $package->name,
                    ],
                ],
            ];

            try {
                $snapToken = Snap::getSnapToken($params);

                return response()->json([
                    'success' => true,
                    'payment_method' => 'qris',
                    'snap_token' => $snapToken,
                    'order_id' => $orderId,
                    'reservation_id' => $reservation->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Midtrans Snap Walkin Error: '.$e->getMessage());
                // Rollback reservation
                $reservation->delete();

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal terhubung ke Midtrans. Silakan coba lagi.',
                ], 500);
            }
        }
    }

    public function scan()
    {
        return view('staff.ticketing.scan');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $reservation = Reservation::where('qr_code', $request->qr_code)->first();

        if (! $reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan atau QR Code tidak valid.',
            ], 404);
        }

        if ($reservation->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Tiket ini sudah digunakan sebelumnya.',
            ], 400);
        }

        if ($reservation->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Tiket ini belum dibayar.',
            ], 400);
        }

        // Mark as completed
        $reservation->status = 'completed';
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil diverifikasi! Selamat datang '.($reservation->user->name ?? $reservation->guest_name),
            'reservation' => $reservation,
        ]);
    }

    public function syncStatus(Reservation $reservation)
    {
        if ($reservation->payment_method !== 'qris' || $reservation->status !== 'pending') {
            return response()->json([
                'success' => true,
                'status' => $reservation->status,
                'payment_status' => $reservation->payment_status,
            ]);
        }

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        try {
            $statusResponse = Transaction::status($reservation->payment_reference);

            $transactionStatus = \is_array($statusResponse) ? ($statusResponse['transaction_status'] ?? null) : ($statusResponse->transaction_status ?? null);
            $paymentType = \is_array($statusResponse) ? ($statusResponse['payment_type'] ?? 'unknown') : ($statusResponse->payment_type ?? 'unknown');

            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                $reservation->payment_status = 'paid';
                $reservation->status = 'completed';
                $reservation->payment_method = $paymentType;
                $reservation->save();
            } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $reservation->payment_status = 'unpaid';
                $reservation->status = 'cancelled';
                $reservation->save();
            }
        } catch (\Exception $e) {
            Log::error('Midtrans walk-in syncStatus error: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'status' => $reservation->status,
            'payment_status' => $reservation->payment_status,
        ]);
    }

    public function checkIn(Reservation $reservation)
    {
        if ($reservation->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya tiket dengan status Menunggu yang dapat di-check-in.',
            ], 400);
        }

        $reservation->status = 'completed';
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil! Pengunjung silakan masuk.',
        ]);
    }

    public function getSnapToken(Reservation $reservation)
    {
        if ($reservation->status !== 'pending' || $reservation->payment_method !== 'qris') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya tiket pending QRIS yang dapat diproses pembayarannya.',
            ], 400);
        }

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $package = $reservation->tourPackage;

        $params = [
            'transaction_details' => [
                'order_id' => $reservation->payment_reference,
                'gross_amount' => $reservation->total_amount,
            ],
            'customer_details' => [
                'first_name' => $reservation->guest_name,
                'email' => $reservation->guest_email ?? 'walkin@example.com',
                'phone' => $reservation->guest_phone ?? '0000000000',
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

        try {
            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Walkin Repay Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke Midtrans. Silakan coba lagi.',
            ], 500);
        }
    }

    public function cancel(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya tiket pending yang dapat dibatalkan.',
            ], 400);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil dibatalkan.',
        ]);
    }
}
