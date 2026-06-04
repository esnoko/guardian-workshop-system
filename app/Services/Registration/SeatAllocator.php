<?php

namespace App\Services\Registration;

use App\Models\WorkshopRegistration;
use App\Models\WorkshopSession;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SeatAllocator
{
    /**
     * @return array<int, string>
     */
    public function allocate(WorkshopSession $session, int $ticketCount): array
    {
        $allocatedSeatCount = WorkshopRegistration::query()
            ->where('workshop_session_id', $session->id)
            ->where('registration_status', '!=', 'cancelled')
            ->whereNotNull('seat_number')
            ->pluck('seat_number')
            ->sum(function (string $seatNumberList) {
                return collect(explode(',', $seatNumberList))
                    ->filter(fn (string $seat) => trim($seat) !== '')
                    ->count();
            });

        $maxCapacity = (int) ($session->max_capacity ?? $session->max_seats ?? 0);
        if ($maxCapacity > 0 && ($allocatedSeatCount + $ticketCount) > $maxCapacity) {
            throw new HttpException(422, 'Not enough seats are available for your selected ticket quantity.');
        }

        $startSeatNumber = $allocatedSeatCount + 1;

        return collect(range($startSeatNumber, $startSeatNumber + $ticketCount - 1))
            ->map(fn (int $seatNumber) => $this->formatSeatCode($session, $seatNumber))
            ->values()
            ->all();
    }

    private function formatSeatCode(WorkshopSession $session, int $seatNumber): string
    {
        $sessionCode = str_pad((string) $session->id, 2, '0', STR_PAD_LEFT);
        $dateCode = $session->session_date->format('dmY');

        return "WS{$sessionCode}-{$dateCode}-" . str_pad((string) $seatNumber, 4, '0', STR_PAD_LEFT);
    }
}
