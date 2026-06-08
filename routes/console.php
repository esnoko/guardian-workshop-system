<?php

use App\Models\WorkshopRegistration;
use App\Models\WorkshopSession;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('registrations:expire-pending', function () {
    $expiryMinutes = (int) config('workshops.registration.pending_expiry_minutes', 120);

    if ($expiryMinutes <= 0) {
        $this->warn('Pending registration expiry is disabled.');

        return self::SUCCESS;
    }

    $cutoff = now()->subMinutes($expiryMinutes);

    $expired = DB::transaction(function () use ($cutoff) {
        $registrations = WorkshopRegistration::query()
            ->where('registration_status', 'pending')
            ->where('created_at', '<=', $cutoff)
            ->lockForUpdate()
            ->get();

        if ($registrations->isEmpty()) {
            return 0;
        }

        $sessionCounts = $registrations
            ->groupBy('workshop_session_id')
            ->map(fn ($items) => $items->count());

        $registrationIds = $registrations->pluck('id');

        WorkshopRegistration::query()
            ->whereIn('id', $registrationIds)
            ->update([
                'registration_status' => 'cancelled',
                'cancelled_at' => now(),
                'admin_notes' => 'Auto-cancelled after payment window expired.',
            ]);

        foreach ($sessionCounts as $sessionId => $count) {
            $session = WorkshopSession::query()->lockForUpdate()->find($sessionId);

            if (!$session) {
                continue;
            }

            $session->registrations_count = max(0, (int) $session->registrations_count - (int) $count);
            $session->save();
        }

        return $registrationIds->count();
    });

    $this->info("Expired {$expired} pending registration(s).");

    return self::SUCCESS;
})->purpose('Cancel pending registrations that exceeded the payment window.');

Schedule::command('registrations:expire-pending')->everyTenMinutes();
