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

        // Batch-load all reserved counts for this date in a single query,
        // then check if at least one timeslot has remaining capacity.
        $capacities = TimeSlotCapacity::query()
            ->whereDate('date', $dateStr)
            ->select('time_slot_id', 'capacity')
            ->get();

        $reservedCounts = TimeSlotCapacity::reservedCountsByDate($dateStr);

        $hasAvailability = $capacities->contains(function ($cap) use ($reservedCounts) {
            $reserved = (int) ($reservedCounts[$cap->time_slot_id] ?? 0);

            return $cap->capacity - $reserved > 0;
        });

        if (! $hasAvailability) {
            $fail(__('zoo.form.errors.no_availability'));
        }
    }
}
