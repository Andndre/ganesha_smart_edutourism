<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function terms(): View
    {
        return view('user.terms');
    }

    public function privacy(): View
    {
        return view('user.privacy');
    }

    public function switchLang(string $locale): RedirectResponse
    {
        if (in_array($locale, ['en', 'id'])) {
            session()->put('locale', $locale);

            if (auth()->check()) {
                auth()->user()->update(['preferred_language' => $locale]);
            }
        }

        return redirect()->back();
    }
}
