<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopRegistration extends Model
{
    protected $fillable = [
        'workshop_session_id',
        'full_name',
        'school_name',
        'email_address',
        'phone_number',
        'province_region',
        'position_role',
        'district',
        'seat_number',
        'reference_number',
        'registration_status',
        'payment_plan',
        'amount_due',
        'amount_paid',
        'registered_at',
        'cancelled_at',
        'admin_notes',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'registered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkshopSession::class, 'workshop_session_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'registration_id');
    }

    public function getPaymentStatusAttribute(): string
    {
        $payments = $this->payments;

        if ($payments->isEmpty()) {
            return 'unpaid';
        }

        if ($this->amount_due > 0 && $this->amount_paid >= $this->amount_due) {
            return 'all_paid_up';
        }

        $completedPayments = $payments->where('status', 'completed')->count();

        if ($completedPayments === 0) {
            return 'pending';
        }

        if ($this->payment_plan === 'full') {
            return 'first_installment_paid';
        }

        return match ($completedPayments) {
            1 => 'first_installment_paid',
            2 => 'second_installment_paid',
            default => 'pending',
        };
    }

    public function generateReferenceNumber(): string
    {
        $surname = collect(explode(' ', $this->full_name))->last();
        $date = $this->session->session_date->format('dmY');
        return strtoupper("{$surname}-{$this->seat_number}-{$date}");
    }

    public function isFullyPaid(): bool
    {
        return $this->amount_due > 0 && $this->amount_paid >= $this->amount_due;
    }
}
