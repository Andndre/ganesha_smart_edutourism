<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// App Routes (using layouts/app.blade.php)
Route::get('/explore', function () {
    return view('welcome', ['title' => 'Explore']);
})->name('explore');

Route::get('/ar-scan', function () {
    return view('welcome', ['title' => 'AR Scan']);
})->name('ar-scan');

Route::get('/umkm', function () {
    return view('welcome', ['title' => 'UMKM']);
})->name('umkm');

Route::get('/profile', function () {
    return view('welcome', ['title' => 'Profil']);
})->name('profile');

// Auth Routes
Route::get('/login', function () {
    return view('welcome', ['title' => 'Login']);
})->name('login');

Route::get('/register', function () {
    return view('welcome', ['title' => 'Register']);
})->name('register');
