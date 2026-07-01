<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the user's profile page.
     */
    public function show(): View
    {
        return view('user.profile.index');
    }

    /**
     * Show the profile edit form.
     */
    public function edit(Request $request): View
    {
        return view('user.profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'preferred_language' => ['nullable', 'string', 'in:id,en'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (\array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'];
        }
        if (\array_key_exists('nationality', $validated)) {
            $user->nationality = $validated['nationality'];
        }
        if (\array_key_exists('preferred_language', $validated)) {
            $user->preferred_language = $validated['preferred_language'];
        }

        if (! empty($validated['password'] ?? null)) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile')->with('success', __('Profil Anda berhasil diperbarui.'));
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Delete old local avatar if exists
        if ($user->avatar_path && ! str_starts_with($user->avatar_path, 'http')) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar_path = $path;
        $user->save();

        return redirect()->route('profile.edit')->with('success', __('Foto profil berhasil diperbarui.'));
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->avatar_path && ! str_starts_with($user->avatar_path, 'http')) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = null;
        $user->save();

        return redirect()->route('profile.edit')->with('success', __('Foto profil berhasil dihapus.'));
    }

    /**
     * Show the user's visited places page.
     */
    public function visited(): Response
    {
        return response()
            ->view('user.profile.visited')
            ->header('Cache-Control', 'no-store, must-revalidate');
    }

    /**
     * Show the help/FAQ page.
     */
    public function help(): View
    {
        return view('user.profile.help');
    }
}
