<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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

            if (Auth::user()->isAdminOrViewer()) {
                return redirect()->intended('/admin/dashboard');
            }

            if (Auth::user()->isUmkmOwner()) {
                return redirect()->intended(route('owner.dashboard'));
            }

            if (Auth::user()->isTicketOfficer()) {
                return redirect()->intended(route('staff.ticketing'));
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => __('Email atau password yang Anda masukkan salah.'),
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
     * Show the UMKM partner registration form.
     */
    public function showUmkmRegister(): View
    {
        return view('auth.register-mitra');
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPassword(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a tourist registration request.
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
     * Handle a UMKM partner registration request.
     */
    public function registerUmkm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::UmkmOwner,
            'email_verified_at' => now(),
        ]);

        $businessName = trim($validated['business_name']);
        $businessTranslations = [
            'id' => $businessName,
            'en' => $businessName,
        ];

        $slug = (new UmkmProfile)->generateCollisionFreeSlug(slugFromTranslatable($businessTranslations));

        UmkmProfile::create([
            'user_id' => $user->id,
            'owner_name' => $user->name,
            'business_name' => $businessTranslations,
            'slug' => $slug,
            'description' => [
                'id' => 'Profil usaha Desa Penglipuran.',
                'en' => 'Penglipuran Village business profile.',
            ],
            'rating' => 5.0,
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('owner.dashboard')->with('success', __('Selamat datang! Akun Mitra UMKM Anda telah berhasil didaftarkan.'));
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Redirect to Google OAuth provider.
     */
    public function redirectToGoogle(Request $request)
    {
        if ($request->query('intent') === 'umkm') {
            session(['auth_intent' => 'umkm']);
        } else {
            session()->forget('auth_intent');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback with auto-linking and UMKM intent.
     */
    public function handleGoogleCallback()
    {
        $isUmkmIntent = session('auth_intent') === 'umkm';
        session()->forget('auth_intent');

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

                if ($isUmkmIntent && ! $user->isUmkmOwner() && ! $user->isAdminOrViewer() && ! $user->isTicketOfficer()) {
                    $updateData['role'] = UserRole::UmkmOwner;
                }

                $user->update($updateData);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'role' => $isUmkmIntent ? UserRole::UmkmOwner : UserRole::Tourist,
                    'email_verified_at' => now(),
                    'password' => null,
                    'avatar_path' => $googleUser->getAvatar(),
                ]);
            }

            // Ensure UMKM owner has a profile initialized
            if ($user->isUmkmOwner() && ! $user->umkmProfile) {
                $ownerName = $user->name ?: 'Mitra Penglipuran';
                $businessName = 'Usaha '.$ownerName;
                $businessTranslations = [
                    'id' => $businessName,
                    'en' => $businessName,
                ];

                $slug = (new UmkmProfile)->generateCollisionFreeSlug(slugFromTranslatable($businessTranslations));

                UmkmProfile::create([
                    'user_id' => $user->id,
                    'owner_name' => $ownerName,
                    'business_name' => $businessTranslations,
                    'slug' => $slug,
                    'description' => [
                        'id' => 'Profil usaha Desa Penglipuran.',
                        'en' => 'Penglipuran Village business profile.',
                    ],
                    'rating' => 5.0,
                    'is_active' => true,
                ]);
            }

            Auth::login($user);

            if ($user->isUmkmOwner()) {
                return redirect()->intended(route('owner.dashboard'))->with('success', __('Selamat datang di Dashboard Mitra UMKM!'));
            }

            if ($user->isAdminOrViewer()) {
                return redirect()->intended('/admin/dashboard');
            }

            if ($user->isTicketOfficer()) {
                return redirect()->intended(route('staff.ticketing'));
            }

            return redirect()->intended('/');

        } catch (\Exception $e) {
            $redirectUrl = $isUmkmIntent ? route('mitra.register') : route('login');

            return redirect($redirectUrl)->withErrors(['email' => __('Gagal login dengan Google. Silakan coba lagi.')]);
        }
    }
}
