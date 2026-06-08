<?php

namespace Tests\Feature;

use App\Models\WorkshopRegistration;
use App\Models\WorkshopSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class WorkshopRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_get_signed_payment_redirect(): void
    {
        $session = WorkshopSession::factory()->create([
            'status' => 'upcoming',
            'max_capacity' => 10,
            'registrations_count' => 0,
        ]);

        $response = $this->post(route('registrations.store', ['session' => $session->id]), [
            'full_name' => 'Elvis Noko Kgomo',
            'school_name' => 'Maowaneng Senior Secondary School',
            'email_address' => 'elvisnoko18@gmail.com',
            'phone_number' => '0764923096',
            'province_region' => 'Limpopo',
            'district' => 'Tshwane',
            'position_role' => 'Teacher',
            'ticket_count' => 3,
            'additional_attendees' => [
                [
                    'full_name' => 'Attendee Two',
                    'school_name' => 'School Two',
                    'phone_number' => '0711111111',
                    'province_region' => 'Gauteng',
                    'district' => 'Tshwane',
                    'position_role' => 'Teacher',
                ],
                [
                    'full_name' => 'Attendee Three',
                    'school_name' => 'School Three',
                    'phone_number' => '0722222222',
                    'province_region' => 'Limpopo',
                    'district' => 'Tshwane',
                    'position_role' => 'HOD',
                ],
            ],
        ]);

        $registration = WorkshopRegistration::query()->first();

        $response->assertRedirect();
        $this->assertNotNull($registration);
        $this->assertStringContainsString(',', (string) $registration->seat_number);
        $this->assertStringContainsString('KGOMO-', (string) $registration->reference_number);

        $redirectTarget = $response->headers->get('Location');
        $this->assertNotNull($redirectTarget);
        $this->assertStringContainsString('signature=', (string) $redirectTarget);
        $this->assertStringContainsString('/payment', (string) $redirectTarget);
    }

    public function test_duplicate_email_for_same_session_is_rejected(): void
    {
        $session = WorkshopSession::factory()->create([
            'status' => 'upcoming',
            'max_capacity' => 10,
            'registrations_count' => 0,
        ]);

        $payload = [
            'full_name' => 'Test User',
            'school_name' => 'Test School',
            'email_address' => 'test@example.com',
            'phone_number' => '0712345678',
            'province_region' => 'Gauteng',
            'district' => 'Tshwane',
            'position_role' => 'Teacher',
            'ticket_count' => 1,
        ];

        $this->post(route('registrations.store', ['session' => $session->id]), $payload)->assertRedirect();

        $second = $this->from(route('workshops.register', ['session' => $session->id]))
            ->post(route('registrations.store', ['session' => $session->id]), $payload);

        $second->assertRedirect(route('workshops.register', ['session' => $session->id]));
        $second->assertSessionHas('error');
        $this->assertEquals(1, WorkshopRegistration::query()->count());
    }

    public function test_registration_is_rejected_when_capacity_would_be_exceeded(): void
    {
        $session = WorkshopSession::factory()->create([
            'status' => 'upcoming',
            'max_capacity' => 2,
            'registrations_count' => 0,
        ]);

        WorkshopRegistration::query()->create([
            'workshop_session_id' => $session->id,
            'full_name' => 'Existing User',
            'school_name' => 'Existing School',
            'email_address' => 'existing@example.com',
            'phone_number' => '0711111111',
            'province_region' => 'Gauteng',
            'district' => 'Tshwane',
            'position_role' => 'Teacher',
            'seat_number' => '300626_1,300626_2',
            'reference_number' => 'EXISTING-REF-30062026',
            'registration_status' => 'pending',
            'amount_due' => 1782.50,
            'registered_at' => now(),
        ]);

        $response = $this->from(route('workshops.register', ['session' => $session->id]))
            ->post(route('registrations.store', ['session' => $session->id]), [
                'full_name' => 'New User',
                'school_name' => 'New School',
                'email_address' => 'new@example.com',
                'phone_number' => '0722222222',
                'province_region' => 'Gauteng',
                'district' => 'Tshwane',
                'position_role' => 'Teacher',
                'ticket_count' => 1,
            ]);

        $response->assertRedirect(route('workshops.register', ['session' => $session->id]));
        $response->assertSessionHas('error');
        $this->assertEquals(1, WorkshopRegistration::query()->count());
    }

    public function test_confirmation_requires_valid_signature(): void
    {
        $session = WorkshopSession::factory()->create();

        $registration = WorkshopRegistration::query()->create([
            'workshop_session_id' => $session->id,
            'full_name' => 'Signed User',
            'school_name' => 'School',
            'email_address' => 'signed@example.com',
            'phone_number' => '0713333333',
            'province_region' => 'Gauteng',
            'district' => 'Tshwane',
            'position_role' => 'Teacher',
            'seat_number' => '300626_1',
            'reference_number' => 'SIGNED-REF-30062026',
            'registration_status' => 'pending',
            'amount_due' => 1782.50,
            'registered_at' => now(),
        ]);

        $unsigned = $this->get(route('registration.confirmation', ['registration' => $registration->id]));
        $unsigned->assertStatus(403);

        $signedUrl = URL::temporarySignedRoute(
            'registration.confirmation',
            now()->addMinutes(5),
            ['registration' => $registration->id]
        );

        $signed = $this->get($signedUrl);
        $signed->assertOk();
    }

    public function test_additional_attendees_are_required_for_multi_ticket_registration(): void
    {
        $session = WorkshopSession::factory()->create([
            'status' => 'upcoming',
            'max_capacity' => 10,
            'registrations_count' => 0,
        ]);

        $response = $this->from(route('workshops.register', ['session' => $session->id]))
            ->post(route('registrations.store', ['session' => $session->id]), [
                'full_name' => 'Primary Booker',
                'school_name' => 'Main School',
                'email_address' => 'booker@example.com',
                'phone_number' => '0764923096',
                'province_region' => 'Limpopo',
                'district' => 'Tshwane',
                'position_role' => 'Teacher',
                'ticket_count' => 3,
            ]);

        $response->assertRedirect(route('workshops.register', ['session' => $session->id]));
        $response->assertSessionHasErrors('additional_attendees');
        $this->assertEquals(0, WorkshopRegistration::query()->count());
    }

    public function test_additional_attendees_are_saved_for_multi_ticket_registration(): void
    {
        $session = WorkshopSession::factory()->create([
            'status' => 'upcoming',
            'max_capacity' => 10,
            'registrations_count' => 0,
        ]);

        $response = $this->post(route('registrations.store', ['session' => $session->id]), [
            'full_name' => 'Primary Booker',
            'school_name' => 'Main School',
            'email_address' => 'booker@example.com',
            'phone_number' => '0764923096',
            'province_region' => 'Limpopo',
            'district' => 'Tshwane',
            'position_role' => 'Teacher',
            'ticket_count' => 3,
            'additional_attendees' => [
                [
                    'full_name' => 'Attendee Two',
                    'school_name' => 'School Two',
                    'phone_number' => '0711111111',
                    'province_region' => 'Gauteng',
                    'district' => 'Tshwane',
                    'position_role' => 'Teacher',
                ],
                [
                    'full_name' => 'Attendee Three',
                    'school_name' => 'School Three',
                    'phone_number' => '0722222222',
                    'province_region' => 'Limpopo',
                    'district' => 'Tshwane',
                    'position_role' => 'HOD',
                ],
            ],
        ]);

        $response->assertRedirect();

        $registration = WorkshopRegistration::query()->first();
        $this->assertNotNull($registration);
        $this->assertIsArray($registration->additional_attendees);
        $this->assertCount(2, $registration->additional_attendees);
        $this->assertSame('Attendee Two', $registration->additional_attendees[0]['full_name']);
        $this->assertSame('booker@example.com', $registration->email_address);
    }
}
