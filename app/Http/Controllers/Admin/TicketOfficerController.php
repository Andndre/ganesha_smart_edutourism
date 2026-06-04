<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TicketOfficerController extends Controller
{
    /**
     * Display a listing of Ticket Officer accounts.
     */
    public function index(): View
    {
        $officers = User::where('role', 'ticket_officer')->orderBy('name')->get();

        return view('admin.ticket-officers.index', compact('officers'));
    }

    /**
     * Store a newly created Ticket Officer in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $validated['role'] = 'ticket_officer';
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.ticket-officers')->with('success', 'Akun petugas tiket berhasil dibuat.');
    }

    /**
     * Update the specified Ticket Officer in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $officer = User::where('role', 'ticket_officer')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $officer->update($validated);

        return redirect()->route('admin.ticket-officers')->with('success', 'Akun petugas tiket berhasil diperbarui.');
    }

    /**
     * Remove the specified Ticket Officer from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $officer = User::where('role', 'ticket_officer')->findOrFail($id);
        $officer->delete();

        return redirect()->route('admin.ticket-officers')->with('success', 'Akun petugas tiket berhasil dihapus.');
    }
}
