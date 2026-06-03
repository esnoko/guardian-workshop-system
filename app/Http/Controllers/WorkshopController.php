<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Models\WorkshopSession;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class WorkshopController extends Controller
{
    public function index(): View
    {
        $workshops = Workshop::query()
            ->with([
                'sessions' => fn ($query) => $query
                    ->orderBy('session_date')
                    ->orderBy('start_time'),
            ])
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        return view('workshops.index', [
            'workshops' => $workshops,
        ]);
    }

    public function register(WorkshopSession $session): View
    {
        $session->loadMissing('workshop');

        $workshop = $session->workshop;
        $ticketPrice = (float) ($workshop?->fee ?? 0);
        $ticketOptions = [1, 2, 3];
        $selectedTickets = 3;
        $subtotal = $ticketPrice * $selectedTickets;
        $grandTotal = $subtotal * 1.15;

        $ticketNumber = $session->session_date->format('dmY') . '_' . Str::padLeft((string) $session->id, 2, '0');
        $baseSeat = $session->session_date->format('dmy') . '_' . Str::padLeft((string) $session->id, 2, '0');

        $seatNumbers = collect([1, 2, 3])->mapWithKeys(function (int $index) use ($baseSeat) {
            return ["Seat {$index}" => $baseSeat . (40 + $index)];
        });

        return view('registration.index', [
            'session' => $session,
            'workshop' => $workshop,
            'ticketOptions' => $ticketOptions,
            'selectedTickets' => $selectedTickets,
            'ticketPrice' => $ticketPrice,
            'subtotal' => $subtotal,
            'grandTotal' => $grandTotal,
            'ticketNumber' => $ticketNumber,
            'seatNumbers' => $seatNumbers,
        ]);
    }
}