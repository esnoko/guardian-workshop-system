<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'duration_hours',
        'fee',
        'benefits',
        'max_attendees',
        'status',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(WorkshopSession::class);
    }
}
