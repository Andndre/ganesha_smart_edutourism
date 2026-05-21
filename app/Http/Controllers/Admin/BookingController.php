<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'Semua');
        $query = Reservation::with('tourPackage')->orderBy('id', 'desc');

        if ($statusFilter === 'Aktif') {
            $query->whereIn('status', ['pending', 'confirmed']);
        } elseif ($statusFilter === 'Selesai') {
            $query->where('status', 'completed');
        } elseif ($statusFilter === 'Dibatalkan') {
            $query->where('status', 'cancelled');
        }

        $bookings = $query->paginate(10)->withQueryString();

        return view('admin.bookings.index', compact('bookings', 'statusFilter'));
    }

    /**
     * Update the status of a booking.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,completed,cancelled'],
            'payment_status' => ['nullable', 'string', 'in:pending,paid,refunded'],
        ]);

        $booking = Reservation::findOrFail($id);
        $booking->update($validated);

        return redirect()->route('admin.bookings')->with('success', 'Status pemesanan berhasil diperbarui.');
    }
}
