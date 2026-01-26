<?php

use App\Http\Controllers\Api\V1\AvailabilityController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\TimeSlotController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/ping', function () {
        return response()->json([
            'ok' => true,
            'message' => 'API v1 is alive',
        ]);
    });

    Route::get('/time-slots', [TimeSlotController::class, 'index']);

    Route::get('/availability', [AvailabilityController::class, 'index']);

    Route::post('/reservations', [ReservationController::class, 'store']);

    Route::get('/reservations/{public_code}', [ReservationController::class, 'show']);
});

