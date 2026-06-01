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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('workshop_registrations')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['payfast', 'payflex'])->default('payfast');
            $table->enum('status', ['pending', 'processing', 'authorized', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->string('gateway')->nullable();
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->string('transaction_reference')->nullable();
            $table->integer('installment_number')->default(1); // 1, 2, or 3
            $table->integer('installment_total')->default(1);
            $table->date('due_date')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('currency', 3)->default('ZAR');
            $table->json('gateway_payload')->nullable();
            $table->text('gateway_response')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
