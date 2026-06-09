<?php

namespace App\Listeners;

use App\Events\RegistrationCreated;
use App\Mail\RegistrationConfirmationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendRegistrationConfirmationEmail
{
    public function handle(RegistrationCreated $event): void
    {
        try {
            $registration = $event->registration;
            $registration->loadMissing(['session.workshop']);

            Log::info('Sending registration confirmation email', [
                'registration_id' => $registration->id,
                'email' => $registration->email_address,
                'reference' => $registration->reference_number,
            ]);

            $confirmationUrl = URL::temporarySignedRoute(
                'registration.confirmation',
                now()->addDays(7),
                ['registration' => $registration->id]
            );

            $paymentUrl = URL::temporarySignedRoute(
                'payment.start',
                now()->addDays(7),
                ['registration' => $registration->id]
            );

            Mail::to($registration->email_address)->send(
                new RegistrationConfirmationMail($registration, $confirmationUrl, $paymentUrl)
            );

            Log::info('Registration confirmation email sent', [
                'registration_id' => $registration->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send registration confirmation email', [
                'registration_id' => $event->registration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
