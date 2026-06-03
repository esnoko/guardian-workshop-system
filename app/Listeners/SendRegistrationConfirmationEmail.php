<?php

namespace App\Listeners;

use App\Events\RegistrationCreated;
use Illuminate\Support\Facades\Log;

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

            // TODO: Implement email notification class
            // Mail::queue(new RegistrationConfirmationEmail($registration));
            
            Log::info('Registration confirmation email queued', [
                'registration_id' => $registration->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to queue registration confirmation email', [
                'registration_id' => $event->registration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
