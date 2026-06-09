<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\WorkshopRegistration;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentIntentService
{
    public function create(WorkshopRegistration $registration, string $method): Payment
    {
        return DB::transaction(function () use ($registration, $method) {
            $registration->refresh();

            if ($method !== 'payfast') {
                throw new HttpException(422, 'This payment method is not available yet.');
            }

            if (! $this->canStartPayment($registration)) {
                throw new HttpException(422, 'This registration is no longer available for payment.');
            }

            $isPayflex = $method === 'payflex';
            $installmentTotal = $isPayflex ? 3 : 1;
            $amount = $isPayflex
                ? round(((float) $registration->amount_due) / 3, 2)
                : (float) $registration->amount_due;

            $registration->update([
                'payment_plan' => $isPayflex ? 'installment' : 'full',
            ]);

            return Payment::create([
                'registration_id' => $registration->id,
                'amount' => $amount,
                'payment_method' => $method,
                'status' => 'processing',
                'gateway' => $method,
                'transaction_reference' => strtoupper($method).'-'.$registration->reference_number,
                'installment_number' => 1,
                'installment_total' => $installmentTotal,
                'currency' => 'ZAR',
                'gateway_payload' => [
                    'registration_reference' => $registration->reference_number,
                    'payment_plan' => $isPayflex ? 'installment' : 'full',
                ],
                'processed_at' => now(),
            ]);
        });
    }

    public function canStartPayment(WorkshopRegistration $registration): bool
    {
        $registration->refresh();

        if ($registration->registration_status !== 'pending') {
            return false;
        }

        if ((float) $registration->amount_due <= 0) {
            return false;
        }

        if ((float) $registration->amount_paid >= (float) $registration->amount_due) {
            return false;
        }

        $expiryMinutes = (int) config('workshops.registration.pending_expiry_minutes', 120);

        return $expiryMinutes <= 0 || $registration->created_at?->gt(now()->subMinutes($expiryMinutes));
    }

    public function finalize(WorkshopRegistration $registration, Payment $payment, bool $success): void
    {
        $this->finalizeFromGateway(
            registration: $registration,
            payment: $payment,
            success: $success,
            gatewayTransactionId: null,
            gatewayResponse: null,
            failureReason: $success ? null : 'Gateway reported failure during redirect simulation.',
        );
    }

    /**
     * @param  array<string, mixed>|null  $gatewayResponse
     */
    public function finalizeFromGateway(
        WorkshopRegistration $registration,
        Payment $payment,
        bool $success,
        ?string $gatewayTransactionId = null,
        ?array $gatewayResponse = null,
        ?string $failureReason = null,
    ): void {
        DB::transaction(function () use ($registration, $payment, $success, $gatewayTransactionId, $gatewayResponse, $failureReason) {
            $payment->refresh();
            $registration->refresh();

            if (in_array($payment->status, ['completed', 'failed', 'cancelled'], true)) {
                return;
            }

            if ($success) {
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'gateway_transaction_id' => $gatewayTransactionId ?: $payment->gateway_transaction_id ?: 'TXN-'.now()->format('YmdHis').'-'.$payment->id,
                    'gateway_response' => $gatewayResponse ? json_encode($gatewayResponse) : $payment->gateway_response,
                ]);

                $newAmountPaid = (float) $registration->amount_paid + (float) $payment->amount;
                $registration->update([
                    'amount_paid' => $newAmountPaid,
                    'registration_status' => $newAmountPaid >= (float) $registration->amount_due ? 'paid' : 'partially_paid',
                ]);

                return;
            }

            $payment->update([
                'status' => 'failed',
                'failure_reason' => $failureReason ?: 'Gateway reported failure during redirect simulation.',
                'gateway_response' => $gatewayResponse ? json_encode($gatewayResponse) : $payment->gateway_response,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>|null  $gatewayResponse
     */
    public function markFailed(Payment $payment, string $reason, ?array $gatewayResponse = null): void
    {
        DB::transaction(function () use ($payment, $reason, $gatewayResponse) {
            $payment->refresh();

            if (in_array($payment->status, ['completed', 'failed', 'cancelled'], true)) {
                return;
            }

            $payment->update([
                'status' => 'failed',
                'failure_reason' => $reason,
                'gateway_response' => $gatewayResponse ? json_encode($gatewayResponse) : $payment->gateway_response,
            ]);
        });
    }
}
