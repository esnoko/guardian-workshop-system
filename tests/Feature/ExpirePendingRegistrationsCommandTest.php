<?php

namespace Tests\Feature;

use App\Models\WorkshopRegistration;
use App\Models\WorkshopSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExpirePendingRegistrationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_cancels_stale_pending_registrations_and_decrements_session_count(): void
    {
        config()->set('workshops.registration.pending_expiry_minutes', 120);

        $now = Carbon::now();

        $session = WorkshopSession::factory()->create([
            'registrations_count' => 2,
            'status' => 'upcoming',
        ]);

        $this->travelTo($now->copy()->subHours(3));

        $stale = WorkshopRegistration::query()->create([
            'workshop_session_id' => $session->id,
            'full_name' => 'Stale User',
            'school_name' => 'Stale School',
            'email_address' => 'stale@example.com',
            'phone_number' => '0710000001',
            'province_region' => 'Gauteng',
            'district' => 'Tshwane',
            'position_role' => 'Teacher',
            'seat_number' => '300626_1',
            'reference_number' => 'STALE-REF-30062026',
            'registration_status' => 'pending',
            'payment_plan' => 'full',
            'amount_due' => 1782.50,
            'amount_paid' => 0,
            'registered_at' => now(),
        ]);

        $this->travelTo($now);

        $fresh = WorkshopRegistration::query()->create([
            'workshop_session_id' => $session->id,
            'full_name' => 'Fresh User',
            'school_name' => 'Fresh School',
            'email_address' => 'fresh@example.com',
            'phone_number' => '0710000002',
            'province_region' => 'Gauteng',
            'district' => 'Tshwane',
            'position_role' => 'Teacher',
            'seat_number' => '300626_2',
            'reference_number' => 'FRESH-REF-30062026',
            'registration_status' => 'pending',
            'payment_plan' => 'full',
            'amount_due' => 1782.50,
            'amount_paid' => 0,
            'registered_at' => now(),
        ]);

        $this->artisan('registrations:expire-pending')->assertSuccessful();

        $stale->refresh();
        $fresh->refresh();
        $session->refresh();

        $this->assertSame('cancelled', $stale->registration_status);
        $this->assertNotNull($stale->cancelled_at);
        $this->assertSame('pending', $fresh->registration_status);
        $this->assertSame(1, (int) $session->registrations_count);

        $this->travelBack();
    }

    public function test_it_does_nothing_when_expiry_is_disabled(): void
    {
        config()->set('workshops.registration.pending_expiry_minutes', 0);

        $session = WorkshopSession::factory()->create([
            'registrations_count' => 1,
            'status' => 'upcoming',
        ]);

        $registration = WorkshopRegistration::query()->create([
            'workshop_session_id' => $session->id,
            'full_name' => 'Pending User',
            'school_name' => 'School',
            'email_address' => 'pending@example.com',
            'phone_number' => '0710000003',
            'province_region' => 'Gauteng',
            'district' => 'Tshwane',
            'position_role' => 'Teacher',
            'seat_number' => '300626_3',
            'reference_number' => 'PENDING-REF-30062026',
            'registration_status' => 'pending',
            'payment_plan' => 'full',
            'amount_due' => 1782.50,
            'amount_paid' => 0,
            'registered_at' => Carbon::now()->subDays(2),
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        $this->artisan('registrations:expire-pending')
            ->expectsOutput('Pending registration expiry is disabled.')
            ->assertSuccessful();

        $registration->refresh();
        $session->refresh();

        $this->assertSame('pending', $registration->registration_status);
        $this->assertSame(1, (int) $session->registrations_count);
    }
}
