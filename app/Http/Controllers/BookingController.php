<?php

namespace App\Http\Controllers;

use App\Mail\ETicketMail;
use App\Models\Reservation;
use App\Models\TourPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

// To be created

class BookingController extends Controller
{
    /**
     * Show the user's booking list.
     */
    public function index(Request $request)
    {
        // 1. Auto-sync pending reservations directly with Midtrans (useful for local development where webhook is unreachable)
        $pendingReservations = Reservation::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->whereNotNull('payment_reference')
            ->get();

        if ($pendingReservations->count() > 0) {
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');

            foreach ($pendingReservations as $reservation) {
                try {
                    $statusResponse = Transaction::status($reservation->payment_reference);

                    $transactionStatus = \is_array($statusResponse) ? ($statusResponse['transaction_status'] ?? null) : ($statusResponse->transaction_status ?? null);
                    $paymentType = \is_array($statusResponse) ? ($statusResponse['payment_type'] ?? 'unknown') : ($statusResponse->payment_type ?? 'unknown');

                    if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                        $reservation->payment_status = 'paid';
                        $reservation->status = Str::startsWith($reservation->payment_reference, 'WALKIN-') ? 'completed' : 'confirmed';
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

        // 2. Fetch reservations with optional filter
        $filter = $request->query('filter', 'semua');
        $query = Reservation::where('user_id', auth()->id())
            ->with('tourPackage')
            ->orderBy('created_at', 'desc');

        if ($filter === 'aktif') {
            $query->where('status', 'confirmed');
        } elseif ($filter === 'selesai') {
            $query->whereIn('status', ['completed', 'cancelled']);
        }

        $reservations = $query->get();

        return view('user.profile.bookings', compact('reservations', 'filter'));
    }

    /**
     * Show the checkout page for a tour package.
     */
    public function checkout(Request $request, $id)
    {
        $package = TourPackage::findOrFail($id);

        return view('user.packages.checkout', compact('package'));
    }

    /**
     * Process the booking and generate Midtrans Snap Token.
     */
    public function process(Request $request, $id)
    {
        $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required',
            'party_size' => 'required|integer|min:1',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:20',
        ]);

        $package = TourPackage::findOrFail($id);

        if ($request->party_size < $package->min_capacity || $request->party_size > $package->max_capacity) {
            return response()->json([
                'success' => false,
                'message' => __('Jumlah peserta harus antara :min dan :max orang.', ['min' => $package->min_capacity, 'max' => $package->max_capacity]),
            ], 422);
        }

        $totalAmount = $package->price * $request->party_size;
        $orderId = 'TKT-'.strtoupper(Str::random(8)).'-'.time();
        $qrCode = Str::uuid()->toString();

        // Create the reservation
        $reservation = new Reservation;
        $reservation->user_id = auth()->id();
        $reservation->guest_name = $request->guest_name;
        $reservation->guest_email = $request->guest_email;
        $reservation->guest_phone = $request->guest_phone;
        $reservation->tour_package_id = $package->id;
        $reservation->reservation_type = 'package';
        $reservation->scheduled_date = $request->scheduled_date;
        $reservation->scheduled_time = $request->scheduled_time;
        $reservation->party_size = $request->party_size;
        $reservation->total_amount = $totalAmount;
        $reservation->status = 'pending';
        $reservation->payment_status = 'unpaid';
        $reservation->payment_reference = $orderId;
        $reservation->qr_code = $qrCode;
        $reservation->save();

        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $totalAmount,
            ],
            'customer_details' => [
                'first_name' => $request->guest_name,
                'email' => $request->guest_email,
                'phone' => $request->guest_phone,
            ],
            'item_details' => [
                [
                    'id' => 'PKG-'.$package->id,
                    'price' => $package->price,
                    'quantity' => $request->party_size,
                    'name' => $package->name,
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
                'reservation_id' => $reservation->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('Gagal terhubung ke sistem pembayaran. Silakan coba lagi.'),
            ], 500);
        }
    }

    /**
     * Handle Midtrans webhook notifications.
     */
    public function webhook(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash('sha512', $request->order_id.$request->status_code.$request->gross_amount.$serverKey);

        if ($hashed == $request->signature_key) {
            $transactionStatus = $request->transaction_status;
            $orderId = $request->order_id;

            $reservation = Reservation::where('payment_reference', $orderId)->first();

            if (! $reservation) {
                return response()->json(['message' => 'Reservation not found'], 404);
            }

            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                if ($reservation->payment_status != 'paid') {
                    $reservation->payment_status = 'paid';
                    $reservation->status = Str::startsWith($reservation->payment_reference, 'WALKIN-') ? 'completed' : 'confirmed';
                    $reservation->payment_method = $request->payment_type;
                    $reservation->save();

                    // Send E-Ticket via Email
                    try {
                        Mail::to($reservation->guest_email)->send(new ETicketMail($reservation));
                    } catch (\Exception $e) {
                        Log::error('Failed to send E-Ticket email: '.$e->getMessage());
                    }
                }
            } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $reservation->payment_status = 'unpaid';
                $reservation->status = 'cancelled';
                $reservation->save();
            } elseif ($transactionStatus == 'pending') {
                $reservation->payment_status = 'unpaid';
                $reservation->status = 'pending';
                $reservation->save();
            }

            return response()->json(['message' => 'Notification processed successfully']);
        }

        return response()->json(['message' => 'Invalid signature'], 403);
    }
}
