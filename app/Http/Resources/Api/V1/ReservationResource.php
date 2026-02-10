<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'public_code' => $this->public_code,
            'date' => $this->date->toDateString(),
            'status' => $this->status->value,
            'timeslot' => [
                'label' => $this->timeSlot->label,
                'start_time' => $this->timeSlot->start_time,
                'end_time' => $this->timeSlot->end_time,
            ],
            'visitors' => $this->visitors->map(fn ($v) => [
                'first_name' => $v->firstname,
                'last_name' => $v->lastname,
                'subscription_number' => $v->subscription_nr,
            ])->values(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
