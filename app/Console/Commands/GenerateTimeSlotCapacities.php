<?php

namespace App\Console\Commands;

use App\Models\TimeSlot;
use App\Models\TimeSlotCapacity;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateTimeSlotCapacities extends Command
{
    protected $signature = 'timeslots:generate-capacities {--days=360} {--start=} {--include-weekends}';

    protected $description = 'Generate time_slot_capacities for each date and timeslot';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $start = $this->option('start')
            ? Carbon::parse($this->option('start'))->startOfDay()
            : now()->startOfDay();

        $end = (clone $start)->addDays($days - 1);

        $timeSlotIds = TimeSlot::where('recurring', true)->pluck('id');
        if ($timeSlotIds->isEmpty()) {
            $this->error('No time slots found in time_slots table.');

            return self::FAILURE;
        }

        $rows = [];
        $chunkSize = 500;
        $upserted = 0;

        $includeWeekends = $this->option('include-weekends');

        for ($date = $start->copy(); $date->lte($end); $date = $date->addDay()) {

            if (! $includeWeekends && $date->isWeekend()) {
                continue;
            }

            foreach ($timeSlotIds as $id) {
                $rows[] = [
                    'date' => $date->toDateString(),
                    'time_slot_id' => $id,
                    'capacity' => TimeSlotCapacity::DEFAULT_CAPACITY,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($rows) >= $chunkSize) {
                    DB::table('time_slot_capacities')->upsert(
                        $rows,
                        ['date', 'time_slot_id'],
                        ['capacity', 'updated_at']
                    );
                    $upserted += count($rows);
                    $rows = [];
                }
            }

            // Progress indicator
            if ((int) $date->diffInDays($start) % 10 === 0) {
                $this->info('Generated up to '.$date->toDateString());
            }
        }

        // Remaining rows
        if (! empty($rows)) {
            DB::table('time_slot_capacities')->upsert(
                $rows,
                ['date', 'time_slot_id'],
                ['capacity', 'updated_at']
            );
            $upserted += count($rows);
        }

        $this->info("Done. Upserted about {$upserted} rows from {$start->toDateString()} to {$end->toDateString()}.");

        return self::SUCCESS;
    }
}
