<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshop_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('workshop_registrations', 'additional_attendees')) {
                $table->json('additional_attendees')->nullable()->after('district');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workshop_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('workshop_registrations', 'additional_attendees')) {
                $table->dropColumn('additional_attendees');
            }
        });
    }
};
