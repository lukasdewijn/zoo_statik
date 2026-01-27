<?php

// database/migrations/xxxx_add_contact_email_and_status_to_reservations_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('contact_email')->nullable()->after('timeslot_id');
            $table->string('status')->default('confirmed')->after('contact_email');
            $table->timestamp('cancelled_at')->nullable()->after('status');

            $table->index(['contact_email']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'status', 'cancelled_at']);
        });
    }
};
