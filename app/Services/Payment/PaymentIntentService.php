<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\WorkshopRegistration;
use Illuminate\Support\Facades\DB;

class PaymentIntentService
{
    public function create(WorkshopRegistration $registration, string $method): Payment
    {
        return DB::transaction(function () use ($registration, $method) {
            $registration->refresh();

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
                'transaction_reference' => strtoupper($method) . '-' . $registration->reference_number,
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

    public function finalize(WorkshopRegistration $registration, Payment $payment, bool $success): void
    {
        DB::transaction(function () use ($registration, $payment, $success) {
            if ($success) {
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'gateway_transaction_id' => $payment->gateway_transaction_id ?: 'TXN-' . now()->format('YmdHis') . '-' . $payment->id,
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
                'failure_reason' => 'Gateway reported failure during redirect simulation.',
            ]);
        });
    }
}
