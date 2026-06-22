<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (request()->has('redirect')) {
            session(['url.intended' => request()->input('redirect')]);
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->isAdmin()) {
                return redirect()->intended('/admin/dashboard');
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPassword(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/');
    }

    /**
     * Guest Walk-In Access Logic
     */
    public function guestAccess($reservationId, $hash)
    {
        $reservation = Reservation::findOrFail($reservationId);

        if (md5($reservation->qr_code) !== $hash) {
            abort(403, 'Link akses tidak valid atau telah kadaluarsa.');
        }

        // Store guest token in session
        session(['guest_token' => $reservation->qr_code, 'guest_name' => $reservation->guest_name]);

        return redirect()->route('home')->with('success', 'Selamat datang, '.$reservation->guest_name.'! Anda dapat mulai menjelajahi Ganesha Smart Edutourism.');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Redirect to Google OAuth provider.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback with auto-linking.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Auto-link: Update existing user with google_id
                // Only update avatar if user doesn't have one yet
                $updateData = ['google_id' => $googleUser->getId()];
                if (! $user->avatar_path && $googleUser->getAvatar()) {
                    $updateData['avatar_path'] = $googleUser->getAvatar();
                }
                $user->update($updateData);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                    'password' => null,
                    'avatar_path' => $googleUser->getAvatar(),
                ]);
            }

            Auth::login($user);

            return redirect()->intended('/');

        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Gagal login dengan Google. Silakan coba lagi.']);
        }
    }
}
