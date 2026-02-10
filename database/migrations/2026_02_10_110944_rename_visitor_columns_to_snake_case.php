<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->renameColumn('firstname', 'first_name');
            $table->renameColumn('lastname', 'last_name');
            $table->renameColumn('subscription_nr', 'subscription_number');
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->renameColumn('first_name', 'firstname');
            $table->renameColumn('last_name', 'lastname');
            $table->renameColumn('subscription_number', 'subscription_nr');
        });
    }
};