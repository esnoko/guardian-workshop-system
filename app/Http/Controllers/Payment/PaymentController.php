<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\WorkshopRegistration;
use App\Services\Payment\PaymentIntentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentIntentService $paymentIntentService,
    ) {
    }

    public function start(WorkshopRegistration $registration): View
    {
        $registration->loadMissing(['session.workshop']);

        $ticketCount = collect(explode(',', (string) $registration->seat_number))
            ->filter(fn (string $seat) => trim($seat) !== '')
            ->count();

        return view('payment.index', [
            'registration' => $registration,
            'session' => $registration->session,
            'workshop' => $registration->session?->workshop,
            'ticketCount' => max(1, $ticketCount),
            'seatNumbers' => collect(explode(',', (string) $registration->seat_number))
                ->filter()
                ->values(),
        ]);
    }

    public function initiate(Request $request, WorkshopRegistration $registration): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:payfast,payflex'],
        ]);

        try {
            $payment = $this->paymentIntentService->create(
                registration: $registration,
                method: $validated['payment_method'],
            );

            if ($validated['payment_method'] === 'payfast') {
                return redirect()->away($this->buildPayfastRedirectUrl($registration, $payment));
            }

            // Placeholder gateway behavior for current phase.
            return redirect()->route('payment.complete', [
                'registration' => $registration->id,
                'payment' => $payment->id,
                'status' => 'success',
            ]);
        } catch (HttpException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Payment initiation failed', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Unable to start payment right now. Please try again.');
        }
    }

    private function buildPayfastRedirectUrl(WorkshopRegistration $registration, Payment $payment): string
    {
        $checkoutUrl = (string) config('services.payfast.checkout_url', 'https://sandbox.payfast.co.za/eng/process');
        $fullNameParts = preg_split('/\s+/', trim((string) $registration->full_name)) ?: [];
        $firstName = (string) ($fullNameParts[0] ?? 'Attendee');
        $lastName = (string) (count($fullNameParts) > 1 ? end($fullNameParts) : $firstName);

        $params = [
            'merchant_id' => (string) config('services.payfast.merchant_id', ''),
            'merchant_key' => (string) config('services.payfast.merchant_key', ''),
            'return_url' => URL::signedRoute('payment.complete', [
                'registration' => $registration->id,
                'payment' => $payment->id,
            ]),
            'cancel_url' => URL::signedRoute('payment.complete', [
                'registration' => $registration->id,
                'payment' => $payment->id,
            ]),
            'notify_url' => route('payment.payfast.itn'),
            'name_first' => $firstName,
            'name_last' => $lastName,
            'email_address' => (string) $registration->email_address,
            'm_payment_id' => (string) $payment->id,
            'amount' => number_format((float) $payment->amount, 2, '.', ''),
            'item_name' => 'Workshop Registration',
            'item_description' => (string) $registration->reference_number,
        ];

        $params['signature'] = $this->generatePayfastSignature($params);

        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        return $checkoutUrl . (str_contains($checkoutUrl, '?') ? '&' : '?') . $query;
    }

    /**
     * @param array<string, string> $params
     */
    private function generatePayfastSignature(array $params): string
    {
        unset($params['signature']);

        $cleanParams = array_filter($params, static fn ($value) => $value !== null && $value !== '');

        $pairs = [];
        foreach ($cleanParams as $key => $value) {
            $pairs[] = $key . '=' . urlencode(trim((string) $value));
        }

        $signatureBase = implode('&', $pairs);
        $passphrase = trim((string) config('services.payfast.passphrase', ''));

        if ($passphrase !== '') {
            $signatureBase .= '&passphrase=' . urlencode($passphrase);
        }

        return md5($signatureBase);
    }

    public function complete(Request $request, WorkshopRegistration $registration, Payment $payment): RedirectResponse
    {
        abort_unless($payment->registration_id === $registration->id, 404);

        $message = match ($payment->status) {
            'completed' => 'Payment completed successfully.',
            'failed', 'cancelled' => 'Payment failed. Please try again.',
            default => 'Payment is being verified. We will confirm shortly.',
        };

        return redirect()
            ->route('registration.confirmation', ['registration' => $registration->id])
            ->with('success', $message);
    }

    public function itn(Request $request): JsonResponse
    {
        $payload = $request->post();

        if (!$this->isValidPayfastItnSignature($payload)) {
            Log::warning('PayFast ITN signature validation failed.', ['payload' => $payload]);

            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $paymentId = (int) ($payload['m_payment_id'] ?? 0);
        $paymentStatus = strtolower(trim((string) ($payload['payment_status'] ?? '')));
        $amountGross = (float) ($payload['amount_gross'] ?? 0);

        $payment = Payment::query()->with('registration')->find($paymentId);

        if (!$payment || !$payment->registration) {
            Log::warning('PayFast ITN payment/registration not found.', ['payload' => $payload]);

            return response()->json(['message' => 'Payment not found'], 404);
        }

        if (!$this->matchesPayment($payment, $amountGross, $payload)) {
            Log::warning('PayFast ITN data mismatch.', [
                'payment_id' => $payment->id,
                'expected_amount' => (float) $payment->amount,
                'received_amount' => $amountGross,
                'payload' => $payload,
            ]);

            $this->paymentIntentService->markFailed(
                payment: $payment,
                reason: 'PayFast ITN data mismatch.',
                gatewayResponse: $payload,
            );

            return response()->json(['message' => 'Data mismatch'], 400);
        }

        $success = $paymentStatus === 'complete';

        $this->paymentIntentService->finalizeFromGateway(
            registration: $payment->registration,
            payment: $payment,
            success: $success,
            gatewayTransactionId: (string) ($payload['pf_payment_id'] ?? ''),
            gatewayResponse: $payload,
            failureReason: $success ? null : ('Gateway status: ' . ($payload['payment_status'] ?? 'unknown')),
        );

        return response()->json(['message' => 'ITN processed']);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function isValidPayfastItnSignature(array $payload): bool
    {
        $incomingSignature = (string) ($payload['signature'] ?? '');
        if ($incomingSignature === '') {
            return false;
        }

        $params = [];
        foreach ($payload as $key => $value) {
            if ($key === 'signature') {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $params[(string) $key] = (string) $value;
        }

        return hash_equals($incomingSignature, $this->generatePayfastSignature($params));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function matchesPayment(Payment $payment, float $amountGross, array $payload): bool
    {
        $merchantId = (string) ($payload['merchant_id'] ?? '');
        $expectedMerchantId = (string) config('services.payfast.merchant_id', '');

        if ($expectedMerchantId !== '' && $merchantId !== $expectedMerchantId) {
            return false;
        }

        if ((int) ($payload['m_payment_id'] ?? 0) !== (int) $payment->id) {
            return false;
        }

        return abs((float) $payment->amount - $amountGross) < 0.01;
    }
}
