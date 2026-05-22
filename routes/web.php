<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CapacityController;
use App\Http\Controllers\Admin\CulturalObjectController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TourRouteController;
use App\Http\Controllers\Admin\UmkmController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\LearningProgressController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Auth Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', function () {
        return view('auth.login');
    })->name('forgot-password');
});

// Authenticated Routes (Users)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Home
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Explore/Map
    Route::get('/explore', [ExploreController::class, 'index'])->name('explore');

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
    Route::get('/learning', [LearningController::class, 'index'])->name('learning');
    Route::get('/learning/{slug}', [LearningController::class, 'show'])->name('learning.show');
    Route::post('/learning/{moduleSlug}/{contentSlug}/quiz', [LearningProgressController::class, 'submitQuiz'])->name('learning.quiz.submit');

    // Tour Packages
    Route::get('/tour-packages', function () {
        return view('pages.packages.index');
    })->name('tour-packages');
    Route::get('/tour-package/{id}', function () {
        return view('pages.packages.show');
    })->name('tour-package');

    // Feedback
    Route::get('/feedback', function () {
        return view('pages.feedback.create');
    })->name('feedback');

    // Profile & E-Ticket
    Route::get('/profile', function () {
        return view('pages.profile.index');
    })->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
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
    Route::get('/profile/settings', [ProfileController::class, 'edit'])->name('settings');
    Route::get('/profile/help', function () {
        return view('home');
    })->name('help');
});

// Offline Page
Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Capacity Zone Routes
    Route::get('/capacity', [CapacityController::class, 'index'])->name('admin.capacity');
    Route::put('/capacity/{id}/thresholds', [CapacityController::class, 'updateThresholds'])->name('admin.capacity.thresholds');

    // Cultural Object Routes
    Route::get('/cultural-objects', [CulturalObjectController::class, 'index'])->name('admin.cultural-objects');
    Route::post('/cultural-objects', [CulturalObjectController::class, 'store'])->name('admin.cultural-objects.store');
    Route::put('/cultural-objects/{id}', [CulturalObjectController::class, 'update'])->name('admin.cultural-objects.update');
    Route::delete('/cultural-objects/{id}', [CulturalObjectController::class, 'destroy'])->name('admin.cultural-objects.destroy');

    // UMKM Routes
    Route::get('/umkm', [UmkmController::class, 'index'])->name('admin.umkm');
    Route::post('/umkm/products', [UmkmController::class, 'store'])->name('admin.umkm.store');
    Route::put('/umkm/products/{id}', [UmkmController::class, 'update'])->name('admin.umkm.update');
    Route::delete('/umkm/products/{id}', [UmkmController::class, 'destroy'])->name('admin.umkm.destroy');

    // Event Routes
    Route::get('/events', [EventController::class, 'index'])->name('admin.events');
    Route::get('/events/create', [EventController::class, 'create'])->name('admin.events.create');
    Route::post('/events', [EventController::class, 'store'])->name('admin.events.store');
    Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('admin.events.edit');
    Route::put('/events/{id}', [EventController::class, 'update'])->name('admin.events.update');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('admin.events.destroy');

    // Tour Route Routes
    Route::get('/tour-routes', [TourRouteController::class, 'index'])->name('admin.tour-routes');
    Route::post('/tour-routes', [TourRouteController::class, 'store'])->name('admin.tour-routes.store');
    Route::put('/tour-routes/{id}', [TourRouteController::class, 'update'])->name('admin.tour-routes.update');
    Route::patch('/tour-routes/{id}/toggle-active', [TourRouteController::class, 'toggleActive'])->name('admin.tour-routes.toggle');
    Route::delete('/tour-routes/{id}', [TourRouteController::class, 'destroy'])->name('admin.tour-routes.destroy');

    // Tour Package Routes
    Route::get('/packages', [PackageController::class, 'index'])->name('admin.packages');
    Route::get('/packages/create', [PackageController::class, 'create'])->name('admin.packages.create');
    Route::post('/packages', [PackageController::class, 'store'])->name('admin.packages.store');
    Route::get('/packages/{id}/edit', [PackageController::class, 'edit'])->name('admin.packages.edit');
    Route::put('/packages/{id}', [PackageController::class, 'update'])->name('admin.packages.update');
    Route::delete('/packages/{id}', [PackageController::class, 'destroy'])->name('admin.packages.destroy');

    // Booking Routes
    Route::get('/bookings', [BookingController::class, 'index'])->name('admin.bookings');
    Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('admin.bookings.status');

    // Feedback Routes
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('admin.feedback');
    Route::post('/feedback/{id}/reply', [FeedbackController::class, 'reply'])->name('admin.feedback.reply');
    Route::patch('/feedback/{id}/toggle-public', [FeedbackController::class, 'togglePublic'])->name('admin.feedback.toggle');
    Route::delete('/feedback/{id}', [FeedbackController::class, 'destroy'])->name('admin.feedback.destroy');

    // Report Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::get('/reports/download', [ReportController::class, 'downloadPdf'])->name('admin.reports.download');
});
