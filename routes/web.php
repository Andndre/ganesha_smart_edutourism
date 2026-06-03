<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CapacityController;
use App\Http\Controllers\Admin\CulturalObjectController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\MapManagerController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TicketingController;
use App\Http\Controllers\Admin\TourRouteController;
use App\Http\Controllers\Admin\UmkmCategoryController;
use App\Http\Controllers\Admin\UmkmController;
use App\Http\Controllers\Api\RoutingController;
use App\Http\Controllers\ARController;
use App\Http\Controllers\AudioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CulturalController;
use App\Http\Controllers\EventController as PublicEventController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Owner\OwnerProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SmartEdutourismController;
use App\Http\Controllers\TourPackageController;
use App\Http\Controllers\UmkmCatalogController;
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

// Public Pages (Guest & Auth)
Route::middleware('redirect.admin')->group(function () {
    // Home
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Guest Walk-In Access
    Route::get('/guest-access/{reservation}/{hash}', [AuthController::class, 'guestAccess'])->name('guest.access');

    // Explore/Map
    Route::get('/explore', [ExploreController::class, 'index'])->name('explore');

    // AR Scan
    Route::get('/ar-scan', [ARController::class, 'index'])->name('ar-scan');

    // UMKM Catalog & Recommendation
    Route::get('/umkm', [UmkmCatalogController::class, 'index'])->name('umkm');
    Route::post('/umkm/recommend', [UmkmCatalogController::class, 'recommend'])->name('umkm.recommend');
    Route::get('/umkm/recommended/{id}', [UmkmCatalogController::class, 'recommended'])->name('umkm.recommended');
    Route::get('/umkm/multi-route', [UmkmCatalogController::class, 'multiRecommended'])->name('umkm.multi_recommended');
    Route::get('/umkm/product/{id}', function () {
        return view('user.umkm.show');
    })->name('umkm-product');

    // Cultural Objects
    Route::get('/cultural', [CulturalController::class, 'index'])->name('cultural-objects');
    Route::get('/cultural/{slug}', [CulturalController::class, 'show'])->name('cultural-object');

    // Events
    Route::get('/events', [PublicEventController::class, 'index'])->name('events');

    // Smart Edutourism
    Route::get('/edutourism', [SmartEdutourismController::class, 'index'])->name('edutourism.index');
    Route::get('/edutourism/routes/{id}/preview', [SmartEdutourismController::class, 'preview'])->name('edutourism.preview');
    Route::post('/edutourism/routes/{id}/start', [SmartEdutourismController::class, 'start'])->name('edutourism.start');
    Route::get('/edutourism/active', [SmartEdutourismController::class, 'active'])->name('edutourism.active');
    Route::get('/edutourism/arrive/{pointId}', [SmartEdutourismController::class, 'arrive'])->name('edutourism.arrive');
    Route::post('/edutourism/quiz/{quizId}/submit', [SmartEdutourismController::class, 'submitQuiz'])->name('edutourism.quiz.submit');

    // Tour Packages
    Route::get('/tour-packages', [TourPackageController::class, 'index'])->name('tour-packages');
    Route::get('/tour-package/{id}', [TourPackageController::class, 'show'])->name('tour-package');

    // Routing API (public — used by /explore which is also public)
    Route::post('/api/routing/directions', [RoutingController::class, 'directions'])->name('routing.directions');
});

// Authenticated Routes (Users)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('redirect.admin')->group(function () {
        // Feedback (Requires login)
        Route::get('/feedback', function () {
            return view('user.feedback.create');
        })->name('feedback');

        // Tour Package Booking
        Route::get('/tour-package/{id}/book', [App\Http\Controllers\BookingController::class, 'checkout'])->name('tour-package.book');
        Route::post('/tour-package/{id}/process', [App\Http\Controllers\BookingController::class, 'process'])->name('tour-package.process');

        // Profile & E-Ticket
        Route::get('/profile', function () {
            return view('user.profile.index');
        })->name('profile');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/profile/bookings', [App\Http\Controllers\BookingController::class, 'index'])->name('bookings');
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
});

// Public Pages
Route::get('/terms', function () {
    return view('user.terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('user.privacy');
})->name('privacy');

// Offline Page
Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// Staff Routes (Admin & Ticket Officer)
Route::prefix('staff')->middleware(['auth', 'staff'])->group(function () {
    Route::get('/ticketing', [TicketingController::class, 'index'])->name('staff.ticketing');
    Route::post('/ticketing/walk-in', [TicketingController::class, 'storeWalkIn'])->name('staff.ticketing.walk-in');
    Route::get('/ticketing/scan', [TicketingController::class, 'scan'])->name('staff.ticketing.scan');
    Route::post('/ticketing/verify', [TicketingController::class, 'verify'])->name('staff.ticketing.verify');
});

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Capacity Zone Routes
    Route::get('/capacity', [CapacityController::class, 'index'])->name('admin.capacity');
    Route::put('/capacity/{id}/thresholds', [CapacityController::class, 'updateThresholds'])->name('admin.capacity.thresholds');

    // Cultural Object Routes
    Route::post('/cultural-objects', [CulturalObjectController::class, 'store'])->name('admin.cultural-objects.store');
    Route::put('/cultural-objects/{id}', [CulturalObjectController::class, 'update'])->name('admin.cultural-objects.update');
    Route::delete('/cultural-objects/{id}', [CulturalObjectController::class, 'destroy'])->name('admin.cultural-objects.destroy');
    Route::post('/cultural-objects/upload-image', [CulturalObjectController::class, 'uploadEditorImage'])->name('admin.cultural-objects.upload-image');

    // Facility Routes
    Route::post('/facilities', [FacilityController::class, 'store'])->name('admin.facilities.store');
    Route::put('/facilities/{facility}', [FacilityController::class, 'update'])->name('admin.facilities.update');
    Route::delete('/facilities/{facility}', [FacilityController::class, 'destroy'])->name('admin.facilities.destroy');

    // UMKM Routes
    Route::get('/umkm', [UmkmController::class, 'index'])->name('admin.umkm');
    Route::post('/umkm/products', [UmkmController::class, 'store'])->name('admin.umkm.store');
    Route::put('/umkm/products/{id}', [UmkmController::class, 'update'])->name('admin.umkm.update');
    Route::delete('/umkm/products/{id}', [UmkmController::class, 'destroy'])->name('admin.umkm.destroy');
    Route::post('/umkm/profiles', [UmkmController::class, 'storeProfile'])->name('admin.umkm.profile.store');
    Route::put('/umkm/profiles/{id}', [UmkmController::class, 'updateProfile'])->name('admin.umkm.profile.update');
    Route::delete('/umkm/profiles/{id}', [UmkmController::class, 'destroyProfile'])->name('admin.umkm.profile.destroy');

    // UMKM Category Routes
    Route::get('/umkm/categories', [UmkmCategoryController::class, 'index'])->name('admin.umkm.categories');
    Route::post('/umkm/categories', [UmkmCategoryController::class, 'store'])->name('admin.umkm.categories.store');
    Route::put('/umkm/categories/{id}', [UmkmCategoryController::class, 'update'])->name('admin.umkm.categories.update');
    Route::delete('/umkm/categories/{id}', [UmkmCategoryController::class, 'destroy'])->name('admin.umkm.categories.destroy');

    // UMKM Owner Routes
    Route::get('/umkm/owners', [UmkmController::class, 'ownersList'])->name('admin.umkm.owners');
    Route::post('/umkm/owners', [UmkmController::class, 'storeOwner'])->name('admin.umkm.owners.store');
    Route::put('/umkm/owners/{id}', [UmkmController::class, 'updateOwner'])->name('admin.umkm.owners.update');
    Route::delete('/umkm/owners/{id}', [UmkmController::class, 'destroyOwner'])->name('admin.umkm.owners.destroy');

    // Map Manager Routes
    Route::get('/map-manager', [MapManagerController::class, 'index'])->name('admin.map-manager');

    // Event Routes
    Route::get('/events', [EventController::class, 'index'])->name('admin.events');
    Route::get('/events/create', [EventController::class, 'create'])->name('admin.events.create');
    Route::post('/events', [EventController::class, 'store'])->name('admin.events.store');
    Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('admin.events.edit');
    Route::put('/events/{id}', [EventController::class, 'update'])->name('admin.events.update');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('admin.events.destroy');

    // Routing API Proxy
    Route::post('/api/routing/directions', [RoutingController::class, 'directions'])->name('admin.routing.directions');

    // Tour Route Routes
    Route::get('/tour-routes', [TourRouteController::class, 'index'])->name('admin.tour-routes');
    Route::get('/tour-routes/create', [TourRouteController::class, 'create'])->name('admin.tour-routes.create');
    Route::get('/tour-routes/{id}/edit', [TourRouteController::class, 'edit'])->name('admin.tour-routes.edit');
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

// Owner Routes
Route::prefix('owner')->middleware(['auth', 'umkm_owner'])->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('owner.dashboard');
    Route::get('/profile', [OwnerDashboardController::class, 'editProfile'])->name('owner.profile');
    Route::put('/profile', [OwnerDashboardController::class, 'updateProfile'])->name('owner.profile.update');
    Route::get('/location', [OwnerDashboardController::class, 'editLocation'])->name('owner.location');
    Route::put('/location', [OwnerDashboardController::class, 'updateLocation'])->name('owner.location.update');

    // Product Routes
    Route::get('/products', [OwnerProductController::class, 'index'])->name('owner.products');
    Route::post('/products', [OwnerProductController::class, 'store'])->name('owner.products.store');
    Route::put('/products/{id}', [OwnerProductController::class, 'update'])->name('owner.products.update');
    Route::delete('/products/{id}', [OwnerProductController::class, 'destroy'])->name('owner.products.destroy');
});

// Audio Range Stream Route (for Chrome seeking/scrubbing support)
Route::get('/audio-stream/{path}', [AudioController::class, 'stream'])
    ->where('path', '.*')
    ->name('audio.stream');

// Language Switcher Route
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        session()->put('locale', $locale);

        if (auth()->check()) {
            auth()->user()->update(['preferred_language' => $locale]);
        }
    }

    return redirect()->back();
})->name('lang.switch');
