<?php

namespace App\Services\Registration;

use App\Models\WorkshopSession;

class RegistrationResponseBuilder
{
    public function build(WorkshopSession $session): array
    {
        $session->loadMissing('workshop');

        $workshop = $session->workshop;
        $ticketPrice = (float) ($workshop?->fee ?? 0);
        $ticketOptions = [1, 2, 3];
        $selectedTickets = 3;
        $vatRate = config('workshops.registration.vat_rate', 0.15);

        $subtotal = $ticketPrice * $selectedTickets;
        $grandTotal = $subtotal * (1 + $vatRate);
        $ticketNumber = $session->session_date->format('dmY') . '_' . str_pad((string) $session->id, 2, '0', STR_PAD_LEFT);

        return [
            'workshop' => $workshop,
            'ticketOptions' => $ticketOptions,
            'selectedTickets' => $selectedTickets,
            'ticketPrice' => $ticketPrice,
            'subtotal' => $subtotal,
            'grandTotal' => $grandTotal,
            'ticketNumber' => $ticketNumber,
            'seatNumbers' => collect([1, 2, 3])->mapWithKeys(fn (int $index) => ["Seat {$index}" => 'Assigned after registration']),
            'vatRate' => $vatRate,
        ];
    }
}
