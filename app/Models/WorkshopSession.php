<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopSession extends Model
{
    protected $fillable = [
        'workshop_id',
        'session_date',
        'start_time',
        'end_time',
        'location',
        'status',
        'max_seats',
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
        if (!$this->max_seats) {
            return PHP_INT_MAX;
        }
        return $this->max_seats - $this->registrations()->count();
    }
}
