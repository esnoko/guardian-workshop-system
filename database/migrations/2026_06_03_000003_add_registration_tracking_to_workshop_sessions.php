<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshop_sessions', function (Blueprint $table) {
            // Add status column if it doesn't exist
            if (!Schema::hasColumn('workshop_sessions', 'status')) {
                $table->string('status')->default('active')->after('end_time');
            }

            // Add registrations count tracking
            if (!Schema::hasColumn('workshop_sessions', 'registrations_count')) {
                $table->unsignedInteger('registrations_count')->default(0)->after('status');
            }

            // Add max capacity
            if (!Schema::hasColumn('workshop_sessions', 'max_capacity')) {
                $table->unsignedInteger('max_capacity')->nullable()->after('registrations_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workshop_sessions', function (Blueprint $table) {
            $table->dropColumn(['status', 'registrations_count', 'max_capacity']);
        });
    }
};
