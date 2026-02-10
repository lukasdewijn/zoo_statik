<?php

use App\Http\Controllers\ReservationCancelController;
use App\Http\Controllers\ReservationSuccessController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/reservation');

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
    ->name('reservations.cancel.do')
    ->middleware('signed');

Route::get('/reservations/cancelled/{public_code}', [ReservationCancelController::class, 'done'])
    ->name('reservations.cancel.done');

require __DIR__.'/settings.php';
