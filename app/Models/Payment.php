<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'registration_id',
        'amount',
        'payment_method',
        'status',
        'gateway',
        'gateway_transaction_id',
        'transaction_reference',
        'installment_number',
        'installment_total',
        'due_date',
        'processed_at',
        'paid_at',
        'currency',
        'gateway_payload',
        'gateway_response',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'processed_at' => 'datetime',
        'paid_at' => 'datetime',
        'gateway_payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(WorkshopRegistration::class, 'registration_id');
    }

    public function isPayFast(): bool
    {
        return $this->payment_method === 'payfast';
    }

    public function isPayflex(): bool
    {
        return $this->payment_method === 'payflex';
    }

    public function isPaid(): bool
    {
        return $this->status === 'completed';
    }

    public function isInstallmentPayment(): bool
    {
        return $this->installment_total > 1;
    }
}
