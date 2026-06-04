<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\WorkshopRegistration;
use App\Models\WorkshopSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_page_loads_with_signed_url(): void
    {
        $registration = $this->createRegistration();

        $url = URL::signedRoute('payment.start', ['registration' => $registration->id]);

        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('Payment');
        $response->assertSee('PayFast');
        $response->assertSee('payflex');
    }

    public function test_payfast_initiation_creates_processing_payment(): void
    {
        $registration = $this->createRegistration();

        $url = URL::signedRoute('payment.initiate', ['registration' => $registration->id]);

        $response = $this->post($url, [
            'payment_method' => 'payfast',
            'payment_plan' => 'full',
        ]);

        $payment = Payment::query()->first();

        $response->assertRedirect();
        $this->assertNotNull($payment);
        $this->assertSame('payfast', $payment->payment_method);
        $this->assertSame('processing', $payment->status);
        $this->assertEquals((float) $registration->amount_due, (float) $payment->amount);
    }

    public function test_payflex_initiation_creates_installment_payment(): void
    {
        $registration = $this->createRegistration();

        $url = URL::signedRoute('payment.initiate', ['registration' => $registration->id]);

        $this->post($url, [
            'payment_method' => 'payflex',
            'payment_plan' => 'installment',
        ])->assertRedirect();

        $payment = Payment::query()->first();

        $this->assertNotNull($payment);
        $this->assertSame('payflex', $payment->payment_method);
        $this->assertSame(3, $payment->installment_total);
        $this->assertSame('installment', WorkshopRegistration::query()->findOrFail($registration->id)->payment_plan);
    }

    public function test_payment_complete_success_updates_registration_status(): void
    {
        $registration = $this->createRegistration();

        $payment = Payment::query()->create([
            'registration_id' => $registration->id,
            'amount' => (float) $registration->amount_due,
            'payment_method' => 'payfast',
            'status' => 'processing',
            'gateway' => 'payfast',
            'transaction_reference' => 'PAYFAST-' . $registration->reference_number,
            'installment_number' => 1,
            'installment_total' => 1,
            'currency' => 'ZAR',
            'processed_at' => now(),
        ]);

        $completeUrl = URL::signedRoute('payment.complete', [
            'registration' => $registration->id,
            'payment' => $payment->id,
            'status' => 'success',
        ]);

        $response = $this->get($completeUrl);

        $response->assertRedirect();

        $registration->refresh();
        $payment->refresh();

        $this->assertSame('completed', $payment->status);
        $this->assertSame('paid', $registration->registration_status);
        $this->assertEquals((float) $registration->amount_due, (float) $registration->amount_paid);
    }

    private function createRegistration(): WorkshopRegistration
    {
        $session = WorkshopSession::factory()->create([
            'status' => 'upcoming',
        ]);

        return WorkshopRegistration::query()->create([
            'workshop_session_id' => $session->id,
            'full_name' => 'Payment Tester',
            'school_name' => 'Test School',
            'email_address' => 'payment@example.com',
            'phone_number' => '0712345678',
            'province_region' => 'Gauteng',
            'district' => 'Tshwane',
            'position_role' => 'Teacher',
            'seat_number' => 'WS01-30062026-0001,WS01-30062026-0002,WS01-30062026-0003',
            'reference_number' => 'TESTER-WS01-30062026-0001-30062026',
            'registration_status' => 'pending',
            'payment_plan' => 'full',
            'amount_due' => 5347.50,
            'amount_paid' => 0,
            'registered_at' => now(),
        ]);
    }
}
