<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Resources\Api\V1\TimeSlotResource;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\JsonResponse;

class TimeSlotController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            TimeSlotResource::collection(
                TimeSlot::where('active', 1)->get()
            )
        );
    }
}
