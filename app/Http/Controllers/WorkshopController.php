<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use Illuminate\Contracts\View\View;

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
}