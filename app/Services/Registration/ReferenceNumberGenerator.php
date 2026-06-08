<?php

namespace App\Services\Registration;

use Illuminate\Support\Str;

class ReferenceNumberGenerator
{
    public function generate(string $fullName, string $firstSeatCode, string $sessionDate): string
    {
        $surname = $this->extractSurnameToken($fullName);
        $seatToken = $this->compactSeatToken($firstSeatCode);

        return "{$surname}-{$seatToken}-{$sessionDate}";
    }

    private function extractSurnameToken(string $fullName): string
    {
        $surname = (string) collect(preg_split('/\s+/', trim($fullName)) ?: [])->last();
        $normalized = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $surname) ?? '');

        return $normalized !== '' ? Str::limit($normalized, 12, '') : 'ATTENDEE';
    }

    private function compactSeatToken(string $firstSeatCode): string
    {
        if (str_contains($firstSeatCode, '_')) {
            $parts = array_values(array_filter(explode('_', trim($firstSeatCode)), fn (string $part) => $part !== ''));

            if (count($parts) >= 2) {
                return Str::upper($parts[0] . '_' . end($parts));
            }
        }

        $parts = array_values(array_filter(explode('-', trim($firstSeatCode)), fn (string $part) => $part !== ''));

        if (count($parts) >= 3) {
            return Str::upper($parts[0] . '-' . end($parts));
        }

        $fallback = Str::upper(preg_replace('/[^A-Za-z0-9-]/', '', $firstSeatCode) ?? '');

        return $fallback !== '' ? $fallback : 'SEAT';
    }
}
