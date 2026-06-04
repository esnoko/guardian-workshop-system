<?php

namespace App\Services\Registration;

use Illuminate\Support\Str;

class ReferenceNumberGenerator
{
    public function generate(string $fullName, string $firstSeatCode, string $sessionDate): string
    {
        $surname = $this->extractSurnameToken($fullName);

        return "{$surname}-{$firstSeatCode}-{$sessionDate}";
    }

    private function extractSurnameToken(string $fullName): string
    {
        $surname = (string) collect(preg_split('/\s+/', trim($fullName)) ?: [])->last();
        $normalized = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $surname) ?? '');

        return $normalized !== '' ? Str::limit($normalized, 12, '') : 'ATTENDEE';
    }
}
