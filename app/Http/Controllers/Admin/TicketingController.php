<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\TourPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketingController extends Controller
{
    public function index()
    {
        $packages = TourPackage::all();
        $reservations = Reservation::with(['user', 'tourPackage'])
            ->whereDate('scheduled_date', today())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.ticketing.index', compact('packages', 'reservations'));
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
        $reservation->status = 'confirmed';
        $reservation->payment_status = 'paid';
        $reservation->qr_code = 'WALKIN-'.strtoupper(Str::random(10));
        $reservation->save();

        // Generate Guest Access Token
        $guestUrl = route('guest.access', ['reservation' => $reservation->id, 'hash' => md5($reservation->qr_code)]);

        return redirect()->route('staff.ticketing')
            ->with('success', 'Tiket Walk-in berhasil dibuat!')
            ->with('guestUrl', $guestUrl)
            ->with('guestQr', $reservation->qr_code);
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
}
