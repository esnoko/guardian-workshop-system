<?php

namespace App\Services\Registration;

use App\Events\RegistrationCreated;
use App\Models\WorkshopRegistration;
use App\Models\WorkshopSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorkshopRegistrationService
{
    public function __construct(
        private readonly SeatAllocator $seatAllocator,
        private readonly ReferenceNumberGenerator $referenceNumberGenerator,
    ) {
    }

    public function canAcceptRegistrations(WorkshopSession $session): bool
    {
        return $session->status === 'upcoming';
    }

    public function createRegistration(WorkshopSession $session, array $validated): WorkshopRegistration
    {
        $email = trim(strtolower($validated['email_address']));
        $ticketCount = (int) $validated['ticket_count'];

        $session->loadMissing('workshop');
        $ticketPrice = (float) ($session->workshop?->fee ?? 0);
        $vatRate = config('workshops.registration.vat_rate', 0.15);
        $subtotal = $ticketPrice * $ticketCount;
        $grandTotal = $subtotal * (1 + $vatRate);

        $registration = DB::transaction(function () use ($session, $validated, $email, $ticketCount, $grandTotal) {
            $lockedSession = WorkshopSession::query()
                ->whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$this->canAcceptRegistrations($lockedSession)) {
                throw new HttpException(422, 'This workshop session is no longer accepting registrations.');
            }

            $existingRegistration = WorkshopRegistration::query()
                ->where('workshop_session_id', $lockedSession->id)
                ->whereRaw('LOWER(email_address) = ?', [$email])
                ->first();

            if ($existingRegistration) {
                throw new HttpException(
                    422,
                    'Your email is already registered for this workshop. Reference: ' . $existingRegistration->reference_number
                );
            }

            $seatCodes = $this->seatAllocator->allocate($lockedSession, $ticketCount);
            $seatNumbers = implode(',', $seatCodes);
            $referenceNumber = $this->referenceNumberGenerator->generate(
                $validated['full_name'],
                (string) ($seatCodes[0] ?? ''),
                $lockedSession->session_date->format('dmY')
            );

            $registration = WorkshopRegistration::create([
                'workshop_session_id' => $lockedSession->id,
                'full_name' => $validated['full_name'],
                'school_name' => $validated['school_name'],
                'email_address' => $email,
                'phone_number' => $validated['phone_number'],
                'province_region' => $validated['province_region'],
                'district' => $validated['district'],
                'position_role' => $validated['position_role'],
                'seat_number' => $seatNumbers,
                'reference_number' => $referenceNumber,
                'registration_status' => 'pending',
                'amount_due' => $grandTotal,
                'registered_at' => now(),
            ]);

            $lockedSession->increment('registrations_count');

            return $registration;
        });

        Log::info('Workshop registration created', [
            'registration_id' => $registration->id,
            'reference' => $registration->reference_number,
            'email' => $email,
            'session_id' => $session->id,
            'ticket_count' => $ticketCount,
            'amount_due' => $grandTotal,
        ]);

        RegistrationCreated::dispatch($registration);

        return $registration;
    }
}
