<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RefundMidtransTransaction;
use App\Jobs\VoidMidtransTransaction;
use App\Models\Reservation;
use App\Models\TourPackage;
use App\Models\User;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            $midtrans = new MidtransService;

            foreach ($pendingWalkIns as $reservation) {
                try {
                    $status = $midtrans->getTransactionStatus($reservation->payment_reference);
                    $transactionStatus = $status['transaction_status'];
                    $paymentType = $status['payment_type'];

                    if (MidtransService::isPaidStatus($transactionStatus)) {
                        $reservation->payment_status = 'paid';
                        $reservation->status = 'completed';
                        $reservation->payment_method = $paymentType;
                        $reservation->save();
                    } elseif (MidtransService::isCancelledStatus($transactionStatus)) {
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
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

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
            'reservationsList'
        ));
    }

    public function stats(Request $request)
    {
        $preset = $request->query('preset', 'today');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Default dates if custom range is selected but not complete
        if ($preset === 'custom') {
            if (! $startDate) {
                $startDate = today()->subDays(7)->format('Y-m-d');
            }
            if (! $endDate) {
                $endDate = today()->format('Y-m-d');
            }
        }

        $query = Reservation::with(['user', 'tourPackage'])
            ->whereIn('status', ['confirmed', 'completed']);

        if ($preset === 'today') {
            $query->whereDate('scheduled_date', today()->format('Y-m-d'));
        } elseif ($preset === 'month') {
            $query->whereBetween('scheduled_date', [
                today()->subDays(30)->startOfDay()->format('Y-m-d H:i:s'),
                today()->endOfDay()->format('Y-m-d H:i:s'),
            ]);
        } elseif ($preset === 'custom') {
            $query->whereBetween('scheduled_date', [
                Carbon::parse($startDate)->startOfDay()->format('Y-m-d H:i:s'),
                Carbon::parse($endDate)->endOfDay()->format('Y-m-d H:i:s'),
            ]);
        }

        $reservations = $query->orderBy('scheduled_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalTicketsSold = $reservations->sum('party_size');
        $totalRevenue = $reservations->sum('total_amount');
        $cashRevenue = $reservations->where('payment_method', 'cash')->sum('total_amount');
        $qrisRevenue = $reservations->where('payment_method', '!=', 'cash')->sum('total_amount');

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
                'scheduled_date' => $res->scheduled_date ? $res->scheduled_date->format('Y-m-d') : '-',
                'time' => $res->created_at->format('H:i'),
            ];
        })->values();

        return view('staff.ticketing.stats', compact(
            'preset',
            'startDate',
            'endDate',
            'totalTicketsSold',
            'totalRevenue',
            'cashRevenue',
            'qrisRevenue',
            'reservationsList'
        ));
    }

    public function storeWalkIn(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'party_size' => 'required|integer|min:1',
            'tour_package_id' => 'required|exists:tour_packages,id',
            'payment_method' => 'required|in:cash,qris',
        ]);

        $package = TourPackage::findOrFail($validated['tour_package_id']);

        $reservation = new Reservation;
        $reservation->fill($validated);
        $reservation->reservation_type = 'package';
        $reservation->scheduled_date = today();
        $reservation->total_amount = $package->price * $validated['party_size'];
        $reservation->qr_code = 'WALKIN-'.strtoupper(Str::random(10));

        // Link to existing user account if email matches
        if (! empty($validated['guest_email'])) {
            $existingUser = User::where('email', $validated['guest_email'])->first();
            if ($existingUser) {
                $reservation->user_id = $existingUser->id;
            }
        }

        if ($validated['payment_method'] === 'cash') {
            $reservation->status = 'completed';
            $reservation->payment_status = 'paid';
            $reservation->payment_method = 'cash';
            $reservation->save();

            return response()->json([
                'success' => true,
                'payment_method' => 'cash',
                'message' => __('Tiket Walk-in (Tunai) berhasil dibuat!'),
            ]);
        } else {
            $orderId = 'WALKIN-'.strtoupper(Str::random(8)).'-'.time();
            $reservation->status = 'pending';
            $reservation->payment_status = 'unpaid';
            $reservation->payment_method = 'qris';
            $reservation->payment_reference = $orderId;
            $reservation->save();

            try {
                $midtrans = new MidtransService;
                $snapToken = $midtrans->getSnapToken(
                    $midtrans->buildSnapParams($reservation, $orderId)
                );

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
                    'message' => __('Gagal terhubung ke Midtrans. Silakan coba lagi.'),
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

        return DB::transaction(function () use ($request) {
            $reservation = Reservation::where('qr_code', $request->qr_code)
                ->lockForUpdate()
                ->first();

            if (! $reservation) {
                return response()->json([
                    'success' => false,
                    'message' => __('Tiket tidak ditemukan atau QR Code tidak valid.'),
                ], 404);
            }

            if ($reservation->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => __('Tiket ini sudah digunakan sebelumnya.'),
                ], 400);
            }

            if ($reservation->payment_status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => __('Tiket ini belum dibayar.'),
                ], 400);
            }

            // Mark as completed with audit trail
            $reservation->update([
                'status' => 'completed',
                'checked_in_at' => now(),
                'checked_in_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Tiket berhasil diverifikasi! Selamat datang :name', ['name' => $reservation->user->name ?? $reservation->guest_name]),
                'reservation' => $reservation,
            ]);
        });
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

        $midtrans = new MidtransService;

        try {
            $status = $midtrans->getTransactionStatus($reservation->payment_reference);
            $transactionStatus = $status['transaction_status'];
            $paymentType = $status['payment_type'];

            if (MidtransService::isPaidStatus($transactionStatus)) {
                $reservation->payment_status = 'paid';
                $reservation->status = 'completed';
                $reservation->payment_method = $paymentType;
                $reservation->save();
            } elseif (MidtransService::isCancelledStatus($transactionStatus)) {
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
                'message' => __('Hanya tiket dengan status Menunggu yang dapat di-check-in.'),
            ], 400);
        }

        $reservation->status = 'completed';
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => __('Check-in berhasil! Pengunjung silakan masuk.'),
        ]);
    }

    public function getSnapToken(Reservation $reservation)
    {
        if ($reservation->status !== 'pending' || $reservation->payment_method !== 'qris') {
            return response()->json([
                'success' => false,
                'message' => __('Hanya tiket pending QRIS yang dapat diproses pembayarannya.'),
            ], 400);
        }

        $midtrans = new MidtransService;
        $params = $midtrans->buildSnapParams($reservation, $reservation->payment_reference);

        try {
            $snapToken = $midtrans->getSnapToken($params);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Walkin Repay Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('Gagal terhubung ke Midtrans. Silakan coba lagi.'),
            ], 500);
        }
    }

    public function cancel(Reservation $reservation)
    {
        if ($reservation->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => __('Tiket yang sudah digunakan tidak dapat dibatalkan.'),
            ], 400);
        }

        if ($reservation->payment_status === 'paid' && $reservation->status === 'confirmed') {
            // ponytail: refund decision by hours-to-scheduled, add full policy later
            $hoursUntilScheduled = $reservation->scheduled_date ? now()->diffInHours($reservation->scheduled_date, false) : 0;

            if ($hoursUntilScheduled >= 24) {
                $reservation->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id(),
                    'cancellation_type' => 'staff_refund',
                    'cancellation_note' => request('reason') ?? 'Full refund (>24h before schedule)',
                ]);

                if ($reservation->payment_reference) {
                    RefundMidtransTransaction::dispatch($reservation->payment_reference, (int) $reservation->total_amount);
                }

                return response()->json([
                    'success' => true,
                    'message' => __('Tiket dibatalkan dengan refund penuh.'),
                ]);
            }

            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancellation_type' => 'staff_cancel',
                'cancellation_note' => request('reason') ?? 'Requires manual refund review (<24h before schedule)',
            ]);

            Log::info('Manual refund review required', [
                'reservation_id' => $reservation->id,
                'hours_until_scheduled' => $hoursUntilScheduled,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Tiket dibatalkan. Refund perlu diproses manual (kurang dari 24 jam).'),
            ]);
        }

        // Cancel pending/unpaid or pending/qris reservations
        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => auth()->id(),
            'cancellation_type' => 'staff_cancel',
            'cancellation_note' => request('reason'),
        ]);

        if ($reservation->payment_reference) {
            VoidMidtransTransaction::dispatch($reservation->payment_reference);
        }

        return response()->json([
            'success' => true,
            'message' => __('Tiket berhasil dibatalkan.'),
        ]);
    }
}
