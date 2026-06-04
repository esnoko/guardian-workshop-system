<?php

namespace Database\Factories;

use App\Models\Workshop;
use App\Models\WorkshopSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkshopSession>
 */
class WorkshopSessionFactory extends Factory
{
    protected $model = WorkshopSession::class;

    public function definition(): array
    {
        return [
            'workshop_id' => Workshop::factory(),
            'session_date' => now()->addDays(7)->toDateString(),
            'start_time' => '08:30:00',
            'end_time' => '12:30:00',
            'location' => 'Johannesburg',
            'status' => 'upcoming',
            'max_seats' => 50,
            'registrations_count' => 0,
            'max_capacity' => 50,
        ];
    }
}
