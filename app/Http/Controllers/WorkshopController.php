<?php

namespace App\Http\Controllers;

use App\Events\RegistrationCreated;
use App\Http\Requests\StoreWorkshopRegistrationRequest;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use App\Models\WorkshopSession;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        // Validate session is open for registrations
        $closedStatuses = ['cancelled', 'completed'];
        if (in_array($session->status, $closedStatuses, true)) {
            throw new HttpException(400, 'This workshop session is not currently accepting registrations.');
        }

        $session->loadMissing('workshop');

        $workshop = $session->workshop;
        $ticketPrice = (float) ($workshop?->fee ?? 0);
        $ticketOptions = [1, 2, 3];
        $selectedTickets = 3;
        $vatRate = config('workshops.registration.vat_rate', 0.15);
        
        $subtotal = $ticketPrice * $selectedTickets;
        $grandTotal = $subtotal * (1 + $vatRate);

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
            'vatRate' => $vatRate,
        ]);
    }

    public function store(StoreWorkshopRegistrationRequest $request, WorkshopSession $session): RedirectResponse
    {
        try {
            // Re-validate session is open for registrations
            $closedStatuses = ['cancelled', 'completed'];
            if (in_array($session->status, $closedStatuses, true)) {
                Log::warning('Registration attempt on closed session', [
                    'session_id' => $session->id,
                    'session_status' => $session->status,
                    'email' => $request->input('email_address'),
                ]);
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'This workshop session is no longer accepting registrations.');
            }

            $validated = $request->validated();
            $email = trim(strtolower($validated['email_address']));
            $ticketCount = (int) $validated['ticket_count'];

            // Check for duplicate registration (same email + same session)
            $existingRegistration = WorkshopRegistration::where('workshop_session_id', $session->id)
                ->whereRaw('LOWER(email_address) = ?', [$email])
                ->where('registration_status', '!=', 'cancelled')
                ->first();

            if ($existingRegistration) {
                Log::warning('Duplicate registration attempt', [
                    'session_id' => $session->id,
                    'email' => $email,
                    'existing_ref' => $existingRegistration->reference_number,
                ]);

                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Your email is already registered for this workshop. Reference: ' . $existingRegistration->reference_number);
            }

            $session->loadMissing('workshop');
            $workshop = $session->workshop;
            $ticketPrice = (float) ($workshop?->fee ?? 0);
            $vatRate = config('workshops.registration.vat_rate', 0.15);
            
            $subtotal = $ticketPrice * $ticketCount;
            $grandTotal = $subtotal * (1 + $vatRate);

            // Generate reference number
            $referenceNumber = $this->generateReferenceNumber();

            // Generate seat numbers
            $baseSeat = $session->session_date->format('dmy') . '_' . Str::padLeft((string) $session->id, 2, '0');
            $seatNumbers = collect(range(1, $ticketCount))
                ->map(fn (int $index) => $baseSeat . (40 + $index))
                ->implode(',');

            // Create registration within transaction
            $registration = DB::transaction(function () use (
                $validated,
                $session,
                $ticketCount,
                $ticketPrice,
                $subtotal,
                $grandTotal,
                $referenceNumber,
                $seatNumbers,
                $email
            ) {
                $reg = WorkshopRegistration::create([
                    'workshop_session_id' => $session->id,
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

                // Increment session registration count
                $session->increment('registrations_count');

                return $reg;
            });

            // Log successful registration
            Log::info('Workshop registration created', [
                'registration_id' => $registration->id,
                'reference' => $registration->reference_number,
                'email' => $email,
                'session_id' => $session->id,
                'ticket_count' => $ticketCount,
                'amount_due' => $grandTotal,
            ]);

            // Dispatch event (queued email will be sent)
            RegistrationCreated::dispatch($registration);

            // Redirect to confirmation/payment
            return redirect()
                ->route('registration.confirmation', ['registration' => $registration->id])
                ->with('success', 'Registration successful! Your reference number is: ' . $referenceNumber);

        } catch (ModelNotFoundException $e) {
            Log::error('Session not found during registration', [
                'session_id' => $session->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('workshops.index')
                ->with('error', 'Workshop session not found. Please try again.');

        } catch (\Throwable $e) {
            Log::error('Registration creation failed', [
                'email' => $request->input('email_address'),
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'An error occurred while processing your registration. Please try again.');
        }
    }

    public function confirmation(WorkshopRegistration $registration): View
    {
        $registration->loadMissing(['session.workshop']);

        return view('registration.confirmation.index', [
            'registration' => $registration,
            'session' => $registration->session,
            'workshop' => $registration->session?->workshop,
            'seatNumbers' => collect(explode(',', (string) $registration->seat_number))
                ->filter()
                ->values(),
        ]);
    }

    private function generateReferenceNumber(): string
    {
        // Format: REF[YYMM]-[6-RANDOM-CHARS]
        $prefix = config('workshops.registration.reference_number_prefix', 'REF');
        $timestamp = now()->format('ym');
        $random = Str::upper(Str::random(6));
        
        return "{$prefix}{$timestamp}-{$random}";
    }
}