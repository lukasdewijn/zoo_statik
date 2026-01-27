<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationSuccessController;
use App\Http\Controllers\ReservationCancelController;
use Illuminate\Support\Facades\URL;

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

Route::get('/reservations/cancel/{public_code}', [ReservationCancelController::class, 'show'])
    ->name('reservations.cancel.show')
    ->middleware('signed');

Route::post('/reservations/cancel/{public_code}', [ReservationCancelController::class, 'cancel'])
    ->name('reservations.cancel.do');

Route::get('/reservations/cancelled/{public_code}', [ReservationCancelController::class, 'done'])
    ->name('reservations.cancel.done');

require __DIR__.'/settings.php';
