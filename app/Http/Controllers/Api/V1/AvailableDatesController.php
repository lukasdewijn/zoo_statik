<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TimeSlotCapacity;
use Illuminate\Http\JsonResponse;

class AvailableDatesController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => TimeSlotCapacity::availableDates(),
        ]);
    }
}