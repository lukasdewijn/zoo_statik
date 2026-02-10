<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $today = now()->toDateString();
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: strftime('%w', date) returns 0 (Sunday) and 6 (Saturday)
            DB::table('time_slot_capacities')
                ->where('date', '>=', $today)
                ->whereRaw("CAST(strftime('%w', date) AS INTEGER) IN (0, 6)")
                ->delete();
        } else {
            // MySQL: DAYOFWEEK returns 1 (Sunday) and 7 (Saturday)
            DB::table('time_slot_capacities')
                ->where('date', '>=', $today)
                ->whereRaw('DAYOFWEEK(date) IN (1, 7)')
                ->delete();
        }
    }

    public function down(): void
    {
        // Cannot restore deleted records
    }
};
