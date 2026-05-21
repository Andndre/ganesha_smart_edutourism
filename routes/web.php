<?php

use Illuminate\Support\Facades\Route;

// Home
Route::get('/', function () {
    return view('home');
})->name('home');

// Explore/Map
Route::get('/explore', function () {
    return view('pages.explore.index');
})->name('explore');

// AR Scan
Route::get('/ar-scan', function () {
    return view('pages.ar.index');
})->name('ar-scan');

// UMKM
Route::get('/umkm', function () {
    return view('pages.umkm.index');
})->name('umkm');

Route::get('/umkm/product/{id}', function () {
    return view('pages.umkm.show');
})->name('umkm-product');

// Profile & E-Ticket
Route::get('/profile', function () {
    return view('pages.profile.index');
})->name('profile');

// Feedback
Route::get('/feedback', function () {
    return view('pages.feedback.create');
})->name('feedback');

Route::get('/profile/bookings', function () {
    return view('bookings.index');
})->name('bookings');

Route::get('/profile/learning', function () {
    return view('learning.index');
})->name('learning-progress');

Route::get('/profile/favorites', function () {
    return view('home');
})->name('favorites');

Route::get('/profile/visited', function () {
    return view('home');
})->name('visited');

Route::get('/profile/settings', function () {
    return view('home');
})->name('settings');

Route::get('/profile/help', function () {
    return view('home');
})->name('help');

// Auth
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/forgot-password', function () {
    return view('auth.login');
})->name('forgot-password');

// Cultural Objects
Route::get('/cultural', function () {
    return view('pages.cultural.index');
})->name('cultural-objects');

Route::get('/cultural/{id}', function () {
    return view('pages.cultural.show');
})->name('cultural-object');

// Events
Route::get('/events', function () {
    return view('pages.events.index');
})->name('events');

// Learning
Route::get('/learning', function () {
    return view('pages.learning.index');
})->name('learning');

Route::get('/learning/module/{id}', function () {
    return view('pages.learning.show');
})->name('learning-module');

// Tour Packages
Route::get('/tour-packages', function () {
    return view('pages.packages.index');
})->name('tour-packages');

Route::get('/tour-package/{id}', function () {
    return view('pages.packages.show');
})->name('tour-package');

// Offline Page
Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/cultural-objects', function () {
        return view('home');
    })->name('admin.cultural-objects');

    Route::get('/umkm', function () {
        return view('home');
    })->name('admin.umkm');

    Route::get('/events', function () {
        return view('home');
    })->name('admin.events');

    Route::get('/events/create', function () {
        return view('home');
    })->name('admin.events.create');

    Route::get('/tour-routes', function () {
        return view('home');
    })->name('admin.tour-routes');

    Route::get('/capacity', function () {
        return view('home');
    })->name('admin.capacity');

    Route::get('/bookings', function () {
        return view('home');
    })->name('admin.bookings');

    Route::get('/packages', function () {
        return view('home');
    })->name('admin.packages');

    Route::get('/packages/create', function () {
        return view('home');
    })->name('admin.packages.create');

    Route::get('/feedback', function () {
        return view('home');
    })->name('admin.feedback');

    Route::get('/reports', function () {
        return view('home');
    })->name('admin.reports');
});

// Logout (for demo purposes)
Route::post('/logout', function () {
    return redirect('/');
})->name('logout');
