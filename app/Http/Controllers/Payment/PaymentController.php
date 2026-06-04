<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\WorkshopRegistration;
use App\Services\Payment\PaymentIntentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

    public function complete(Request $request, WorkshopRegistration $registration, Payment $payment): RedirectResponse
    {
        abort_unless($payment->registration_id === $registration->id, 404);

        $isSuccess = $request->query('status', 'success') === 'success';

        $this->paymentIntentService->finalize($registration, $payment, $isSuccess);

        return redirect()
            ->route('registration.confirmation', ['registration' => $registration->id])
            ->with('success', $isSuccess ? 'Payment completed successfully.' : 'Payment failed. Please try again.');
    }
}
