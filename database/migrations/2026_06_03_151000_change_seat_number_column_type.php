<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workshop_registrations', function (Blueprint $table) {
            // Change seat_number from integer to string to store comma-separated seat codes
            $table->string('seat_number', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_registrations', function (Blueprint $table) {
            $table->integer('seat_number')->nullable()->change();
        });
    }
};
