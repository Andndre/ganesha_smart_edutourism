<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read UmkmProfile|null $profile
 * @property-read User|null $user
 */
abstract class BaseOwnerController extends Controller
{
    private ?UmkmProfile $profileCache = null;

    private bool $profileResolved = false;

    public function __get(string $name): mixed
    {
        return match ($name) {
            'profile' => $this->resolveProfile(),
            'user' => Auth::user(),
            default => null,
        };
    }

    /**
     * Require a profile or abort with redirect + error message.
     */
    protected function requireProfile(string $route = 'owner.profile'): UmkmProfile
    {
        $profile = $this->resolveProfile();
        if (! $profile) {
            abort(redirect()->route($route)->with('error', __('Silakan buat profil toko terlebih dahulu.')));
        }

        return $profile;
    }

    private function resolveProfile(): ?UmkmProfile
    {
        if (! $this->profileResolved) {
            $this->profileCache = Auth::user()?->umkmProfile;
            $this->profileResolved = true;
        }

        return $this->profileCache;
    }
}
