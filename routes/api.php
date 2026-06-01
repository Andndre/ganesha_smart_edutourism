<?php

use App\Http\Controllers\ARController;
use App\Http\Controllers\BookingController;

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
