<?php

use App\Http\Controllers\ARController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\TrackingController;

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

Route::post('/midtrans/webhook', [BookingController::class, 'webhook']);

Route::get('/ar/model', [ARController::class, 'getModel']);

Route::post('/tracking/ping', [TrackingController::class, 'ping']);
Route::post('/tracking/leave', [TrackingController::class, 'leave']);
