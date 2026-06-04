<?php

namespace Database\Factories;

use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workshop>
 */
class WorkshopFactory extends Factory
{
    protected $model = Workshop::class;

    public function definition(): array
    {
        return [
            'title' => 'Guardian of Privacy',
            'description' => 'Digital ethics and mandatory reporting workshop.',
            'duration_hours' => 4,
            'fee' => 1550.00,
            'benefits' => 'CPTD points and practical safeguarding training.',
            'max_attendees' => 100,
            'status' => 'active',
        ];
    }
}
