<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\DB;

class GenerateTimeSlotCapacities extends Command
{
    protected $signature = 'timeslots:generate-capacities {--days=360} {--start=}';
    protected $description = 'Generate time_slot_capacities for each date and timeslot';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $start = $this->option('start')
            ? Carbon::parse($this->option('start'))->startOfDay()
            : now()->startOfDay();

        $end = (clone $start)->addDays($days - 1);

        $timeSlotIds = TimeSlot::query()->pluck('id');
        if ($timeSlotIds->isEmpty()) {
            $this->error('No time slots found in time_slots table.');
            return self::FAILURE;
        }

        $rows = [];
        $chunkSize = 500;
        $upserted = 0;

        for ($date = $start->copy(); $date->lte($end); $date = $date->addDay()) {

            foreach ($timeSlotIds as $id) {
                $rows[] = [
                    'date' => $date->toDateString(),
                    'time_slot_id' => $id,
                    'capacity' => 200,
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

            // mini progress zodat je ziet dat hij werkt
            if ((int)$date->diffInDays($start) % 10 === 0) {
                $this->info('Generated up to ' . $date->toDateString());
            }
        }

        // laatste restje
        if (!empty($rows)) {
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
