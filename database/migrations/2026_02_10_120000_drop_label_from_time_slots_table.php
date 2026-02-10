<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }

    public function down(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->string('label')->after('id')->default('');
        });

        // Repopulate label from start_time and end_time
        DB::table('time_slots')->update([
            'label' => DB::raw("CONCAT(start_time, ' - ', end_time)"),
        ]);
    }
};
