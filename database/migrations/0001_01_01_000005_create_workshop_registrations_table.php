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
        Schema::create('workshop_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_session_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('school_name');
            $table->string('email_address');
            $table->string('phone_number');
            $table->string('province_region');
            $table->string('position_role');
            $table->string('district');
            $table->integer('seat_number')->nullable();
            $table->string('reference_number')->unique()->nullable();
            $table->enum('registration_status', ['pending', 'registered', 'partially_paid', 'paid', 'cancelled', 'completed'])->default('pending');
            $table->enum('payment_plan', ['full', 'installment'])->default('full');
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->unique(['workshop_session_id', 'email_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_registrations');
    }
};
