<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationSuccessController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/reservation', function () {
    return view('pages.zoo-reservation');
});

Route::get('/reservations/success/{public_code}', ReservationSuccessController::class)
    ->name('reservations.success');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
