<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\WorkshopRegistration;
use App\Models\WorkshopSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.payfast.merchant_id', '10000100');
        config()->set('services.payfast.merchant_key', '46f0cd694581a');
        config()->set('services.payfast.passphrase', 'sandbox-passphrase');
        config()->set('services.payfast.checkout_url', 'https://sandbox.payfast.co.za/eng/process');
        config()->set('services.payfast.validate_itn_ip', false);
        config()->set('services.payfast.validate_itn_server', false);
        config()->set('services.payflex.enabled', false);
    }

    public function test_payment_page_loads_with_signed_url(): void
    {
        $registration = $this->createRegistration();

        $url = URL::signedRoute('payment.start', ['registration' => $registration->id]);

        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('Payment');
        $response->assertSee('PayFast');
        $response->assertDontSee('payment-method-payflex');
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
        $location = (string) $response->headers->get('Location');

        $response->assertRedirect();
        $this->assertNotNull($response->headers->get('Location'));
        $this->assertStringStartsWith('https://sandbox.payfast.co.za/eng/process?', $location);
        $this->assertStringContainsString('signature=', $location);
        $this->assertNotNull($payment);
        $this->assertSame('payfast', $payment->payment_method);
        $this->assertSame('processing', $payment->status);
        $this->assertEquals((float) $registration->amount_due, (float) $payment->amount);
    }

    public function test_payflex_is_rejected_when_not_enabled(): void
    {
        $registration = $this->createRegistration();

        $url = URL::signedRoute('payment.initiate', ['registration' => $registration->id]);

        $this->post($url, [
            'payment_method' => 'payflex',
            'payment_plan' => 'installment',
        ])->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Payment::query()->count());
        $this->assertSame('full', WorkshopRegistration::query()->findOrFail($registration->id)->payment_plan);
    }

    public function test_payfast_itn_success_finalizes_payment_and_registration(): void
    {
        $registration = $this->createRegistration();

        $payment = Payment::query()->create([
            'registration_id' => $registration->id,
            'amount' => (float) $registration->amount_due,
            'payment_method' => 'payfast',
            'status' => 'processing',
            'gateway' => 'payfast',
            'transaction_reference' => 'PAYFAST-'.$registration->reference_number,
            'installment_number' => 1,
            'installment_total' => 1,
            'currency' => 'ZAR',
            'processed_at' => now(),
        ]);

        $payload = [
            'merchant_id' => '10000100',
            'merchant_key' => '46f0cd694581a',
            'm_payment_id' => (string) $payment->id,
            'amount_gross' => number_format((float) $payment->amount, 2, '.', ''),
            'payment_status' => 'COMPLETE',
            'pf_payment_id' => 'PF123456',
            'item_name' => 'Workshop Registration',
        ];

        $payload['signature'] = $this->generateSignatureForTest($payload);

        $response = $this->post(route('payment.payfast.itn'), $payload);

        $response->assertOk();

        $payment->refresh();
        $registration->refresh();

        $this->assertSame('completed', $payment->status);
        $this->assertSame('PF123456', $payment->gateway_transaction_id);
        $this->assertSame('paid', $registration->registration_status);
        $this->assertEquals((float) $registration->amount_due, (float) $registration->amount_paid);
    }

    public function test_payfast_itn_with_invalid_signature_is_rejected(): void
    {
        $registration = $this->createRegistration();

        $payment = Payment::query()->create([
            'registration_id' => $registration->id,
            'amount' => (float) $registration->amount_due,
            'payment_method' => 'payfast',
            'status' => 'processing',
            'gateway' => 'payfast',
            'transaction_reference' => 'PAYFAST-'.$registration->reference_number,
            'installment_number' => 1,
            'installment_total' => 1,
            'currency' => 'ZAR',
            'processed_at' => now(),
        ]);

        $payload = [
            'merchant_id' => '10000100',
            'merchant_key' => '46f0cd694581a',
            'm_payment_id' => (string) $payment->id,
            'amount_gross' => number_format((float) $payment->amount, 2, '.', ''),
            'payment_status' => 'COMPLETE',
            'signature' => 'invalidsignature',
        ];

        $response = $this->post(route('payment.payfast.itn'), $payload);

        $response->assertStatus(400);

        $payment->refresh();
        $registration->refresh();

        $this->assertSame('processing', $payment->status);
        $this->assertSame('pending', $registration->registration_status);
    }

    public function test_payfast_itn_is_idempotent_for_repeated_callbacks(): void
    {
        $registration = $this->createRegistration();

        $payment = Payment::query()->create([
            'registration_id' => $registration->id,
            'amount' => (float) $registration->amount_due,
            'payment_method' => 'payfast',
            'status' => 'processing',
            'gateway' => 'payfast',
            'transaction_reference' => 'PAYFAST-'.$registration->reference_number,
            'installment_number' => 1,
            'installment_total' => 1,
            'currency' => 'ZAR',
            'processed_at' => now(),
        ]);

        $payload = [
            'merchant_id' => '10000100',
            'merchant_key' => '46f0cd694581a',
            'm_payment_id' => (string) $payment->id,
            'amount_gross' => number_format((float) $payment->amount, 2, '.', ''),
            'payment_status' => 'COMPLETE',
            'pf_payment_id' => 'PF123456',
        ];

        $payload['signature'] = $this->generateSignatureForTest($payload);

        $this->post(route('payment.payfast.itn'), $payload)->assertOk();
        $this->post(route('payment.payfast.itn'), $payload)->assertOk();

        $payment->refresh();
        $registration->refresh();

        $this->assertSame('completed', $payment->status);
        $this->assertEquals((float) $registration->amount_due, (float) $registration->amount_paid);
    }

    public function test_payment_complete_route_does_not_finalize_without_gateway_callback(): void
    {
        $registration = $this->createRegistration();

        $payment = Payment::query()->create([
            'registration_id' => $registration->id,
            'amount' => (float) $registration->amount_due,
            'payment_method' => 'payfast',
            'status' => 'processing',
            'gateway' => 'payfast',
            'transaction_reference' => 'PAYFAST-'.$registration->reference_number,
            'installment_number' => 1,
            'installment_total' => 1,
            'currency' => 'ZAR',
            'processed_at' => now(),
        ]);

        $completeUrl = URL::signedRoute('payment.complete', [
            'registration' => $registration->id,
            'payment' => $payment->id,
        ]);

        $response = $this->get($completeUrl);

        $response->assertRedirect();

        $registration->refresh();
        $payment->refresh();

        $this->assertSame('processing', $payment->status);
        $this->assertSame('pending', $registration->registration_status);
        $this->assertEquals(0.0, (float) $registration->amount_paid);
    }

    public function test_expired_registration_cannot_start_payment(): void
    {
        config()->set('workshops.registration.pending_expiry_minutes', 120);

        $registration = $this->createRegistration([
            'created_at' => Carbon::now()->subHours(3),
            'updated_at' => Carbon::now()->subHours(3),
        ]);

        $url = URL::signedRoute('payment.start', ['registration' => $registration->id]);

        $this->get($url)
            ->assertRedirect(route('workshops.index'))
            ->assertSessionHas('error');
    }

    public function test_cancelled_registration_cannot_create_payment(): void
    {
        $registration = $this->createRegistration([
            'registration_status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $url = URL::signedRoute('payment.initiate', ['registration' => $registration->id]);

        $this->from(URL::signedRoute('payment.start', ['registration' => $registration->id]))
            ->post($url, ['payment_method' => 'payfast'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, Payment::query()->count());
    }

    private function createRegistration(array $overrides = []): WorkshopRegistration
    {
        $session = WorkshopSession::factory()->create([
            'status' => 'upcoming',
        ]);

        $registration = WorkshopRegistration::query()->create([
            'workshop_session_id' => $session->id,
            'full_name' => 'Payment Tester',
            'school_name' => 'Test School',
            'email_address' => 'payment@example.com',
            'phone_number' => '0712345678',
            'province_region' => 'Gauteng',
            'district' => 'Tshwane',
            'position_role' => 'Teacher',
            'seat_number' => '080626_1,080626_2,080626_3',
            'reference_number' => 'TESTER-WS01-0001-08062026',
            'registration_status' => 'pending',
            'payment_plan' => 'full',
            'amount_due' => 5347.50,
            'amount_paid' => 0,
            'registered_at' => now(),
        ]);

        if ($overrides !== []) {
            $registration->forceFill($overrides)->save();
            $registration->refresh();
        }

        return $registration;
    }

    /**
     * @param  array<string, string>  $payload
     */
    private function generateSignatureForTest(array $payload): string
    {
        $params = Arr::except($payload, ['signature']);

        $pairs = [];
        foreach ($params as $key => $value) {
            if ($value === '') {
                continue;
            }

            $pairs[] = $key.'='.urlencode(trim((string) $value));
        }

        $signatureBase = implode('&', $pairs);
        $signatureBase .= '&passphrase='.urlencode('sandbox-passphrase');

        return md5($signatureBase);
    }
}
