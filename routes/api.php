<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CulturalObjectController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\FeedbackController;
use App\Http\Controllers\Api\V1\LearningController;
use App\Http\Controllers\Api\V1\MapController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\TourPackageController;
use App\Http\Controllers\Api\V1\TourRouteController;
use App\Http\Controllers\Api\V1\UmkmController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
    });

    // Cultural Heritage
    Route::get('/cultural-objects', [CulturalObjectController::class, 'index']);
    Route::get('/cultural-objects/{slug}', [CulturalObjectController::class, 'show']);
    Route::get('/cultural-objects/{slug}/stories', [CulturalObjectController::class, 'stories']);
    Route::get('/cultural-objects/{slug}/time-travel', [CulturalObjectController::class, 'timeTravel']);

    // Maps & Navigation
    Route::get('/map/locations', [MapController::class, 'locations']);
    Route::get('/map/locations/nearby', [MapController::class, 'nearby']);
    Route::get('/tour-routes', [TourRouteController::class, 'index']);
    Route::get('/tour-routes/{slug}', [TourRouteController::class, 'show']);

    // Events
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{slug}', [EventController::class, 'show']);

    // Learning
    Route::get('/learning-modules', [LearningController::class, 'index']);
    Route::get('/learning-modules/{slug}', [LearningController::class, 'show']);

    // UMKM
    Route::get('/umkm', [UmkmController::class, 'index']);
    Route::get('/umkm/{slug}', [UmkmController::class, 'show']);
    Route::get('/umkm/{slug}/products', [UmkmController::class, 'products']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    // Reservations
    Route::get('/tour-packages', [TourPackageController::class, 'index']);
    Route::get('/tour-packages/{slug}', [TourPackageController::class, 'show']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations/{qr_code}', [ReservationController::class, 'show']);

    // Feedback
    Route::post('/feedback', [FeedbackController::class, 'store']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::get('/profile/tickets', [ProfileController::class, 'tickets']);
        Route::get('/profile/orders', [ProfileController::class, 'orders']);
        Route::get('/profile/learning-progress', [ProfileController::class, 'learningProgress']);
    });
});
