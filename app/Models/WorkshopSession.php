<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'session_date',
        'start_time',
        'end_time',
        'location',
        'status',
        'max_seats',
        'max_capacity',
    ];

    protected $casts = [
        'session_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(WorkshopRegistration::class);
    }

    public function getAvailableSeats(): int
    {
        $capacity = (int) ($this->max_capacity ?? $this->max_seats ?? 0);

        if (! $capacity) {
            return PHP_INT_MAX;
        }

        return $capacity - $this->registrations()
            ->where('registration_status', '!=', 'cancelled')
            ->count();
    }
}
