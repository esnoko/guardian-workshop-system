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
        $allocatedSeatNumbers = WorkshopRegistration::query()
            ->where('workshop_session_id', $session->id)
            ->where('registration_status', '!=', 'cancelled')
            ->whereNotNull('seat_number')
            ->pluck('seat_number')
            ->flatMap(function (string $seatNumberList) {
                return collect(explode(',', $seatNumberList))
                    ->map(fn (string $seat) => trim($seat))
                    ->filter()
                    ->map(fn (string $seat) => $this->extractSeatNumber($seat))
                    ->filter(fn (?int $seatNumber) => $seatNumber !== null);
            })
            ->values();

        $maxCapacity = (int) ($session->max_capacity ?? $session->max_seats ?? 0);
        if ($maxCapacity > 0 && ($allocatedSeatNumbers->count() + $ticketCount) > $maxCapacity) {
            throw new HttpException(422, 'Not enough seats are available for your selected ticket quantity.');
        }

        $startSeatNumber = ((int) $allocatedSeatNumbers->max()) + 1;

        return collect(range($startSeatNumber, $startSeatNumber + $ticketCount - 1))
            ->map(fn (int $seatNumber) => $this->formatSeatCode($session, $seatNumber))
            ->values()
            ->all();
    }

    private function formatSeatCode(WorkshopSession $session, int $seatNumber): string
    {
        $dateCode = $session->session_date->format('dmy');

        return "{$dateCode}_{$seatNumber}";
    }

    private function extractSeatNumber(string $seatCode): ?int
    {
        if (preg_match('/(?:_|-)(\d+)$/', $seatCode, $matches) === 1) {
            return (int) $matches[1];
        }

        if (preg_match('/^\d+$/', $seatCode) === 1) {
            return (int) $seatCode;
        }

        return null;
    }
}
