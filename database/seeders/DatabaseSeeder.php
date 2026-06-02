<?php

namespace Database\Seeders;

use App\Models\Workshop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $workshop = Workshop::query()->updateOrCreate([
            'title' => 'Guardian of Privacy: Digital Ethics and Mandatory Reporting',
        ], [
            'title' => 'Guardian of Privacy: Digital Ethics and Mandatory Reporting',
            'description' => 'The SACE-endorsed short course helps educators and stakeholders build practical knowledge on digital ethics, learner confidentiality, and mandatory reporting in educational environments.',
            'duration_hours' => 4,
            'fee' => 1550,
            'benefits' => 'Earn up to 5 CPTD points on successful completion.',
            'status' => 'active',
        ]);

        $workshop->sessions()->delete();

        $sessions = [
            ['date' => '2026-06-29', 'start' => '08:30:00', 'end' => '12:30:00'],
            ['date' => '2026-06-30', 'start' => '08:30:00', 'end' => '12:30:00'],
            ['date' => '2026-07-01', 'start' => '08:30:00', 'end' => '12:30:00'],
            ['date' => '2026-07-02', 'start' => '13:00:00', 'end' => '17:30:00'],
            ['date' => '2026-07-03', 'start' => '13:00:00', 'end' => '17:30:00'],
            ['date' => '2026-07-06', 'start' => '08:30:00', 'end' => '12:30:00'],
            ['date' => '2026-07-07', 'start' => '08:30:00', 'end' => '12:30:00'],
            ['date' => '2026-07-08', 'start' => '08:30:00', 'end' => '12:30:00'],
            ['date' => '2026-07-09', 'start' => '13:00:00', 'end' => '17:30:00'],
            ['date' => '2026-07-10', 'start' => '13:00:00', 'end' => '17:30:00'],
        ];

        foreach ($sessions as $session) {
            $workshop->sessions()->create([
                'session_date' => $session['date'],
                'start_time' => $session['start'],
                'end_time' => $session['end'],
                'status' => 'upcoming',
                'max_seats' => 30,
            ]);
        }
    }
}
