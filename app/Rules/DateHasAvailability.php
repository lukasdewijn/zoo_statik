<?php

namespace App\Rules;

use App\Models\TimeSlotCapacity;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DateHasAvailability implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $date = Carbon::parse($value);
        } catch (\Exception) {
            return; // let the 'date' rule handle invalid formats
        }

        $dateStr = $date->toDateString();

        // Check if at least one timeslot has remaining capacity on this date
        $hasAvailability = false;

        $capacities = TimeSlotCapacity::query()
            ->whereDate('date', $dateStr)
            ->select('time_slot_id', 'capacity')
            ->get();

        foreach ($capacities as $cap) {
            $reserved = TimeSlotCapacity::reservedCount($dateStr, $cap->time_slot_id);
            if ($cap->capacity - $reserved > 0) {
                $hasAvailability = true;
                break;
            }
        }

        if (! $hasAvailability) {
            $fail(__('zoo.form.errors.no_availability'));
        }
    }
}
