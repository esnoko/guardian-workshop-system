<?php

namespace App\Http\Controllers;

use App\Events\RegistrationCreated;
use App\Http\Requests\StoreWorkshopRegistrationRequest;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use App\Models\WorkshopSession;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
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
        // Accept registrations only for upcoming sessions.
        if (!$this->canAcceptRegistrations($session->status)) {
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

        $seatNumbers = collect([1, 2, 3])->mapWithKeys(function (int $index) {
            return ["Seat {$index}" => 'Assigned after registration'];
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
            // Re-validate session is open for registrations.
            if (!$this->canAcceptRegistrations($session->status)) {
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

            $session->loadMissing('workshop');
            $workshop = $session->workshop;
            $ticketPrice = (float) ($workshop?->fee ?? 0);
            $vatRate = config('workshops.registration.vat_rate', 0.15);
            
            $subtotal = $ticketPrice * $ticketCount;
            $grandTotal = $subtotal * (1 + $vatRate);

            // Create registration within transaction
            $registration = DB::transaction(function () use (
                $validated,
                $session,
                $ticketCount,
                $ticketPrice,
                $subtotal,
                $grandTotal,
                $email
            ) {
                $lockedSession = WorkshopSession::query()
                    ->whereKey($session->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$this->canAcceptRegistrations($lockedSession->status)) {
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

                $allocatedSeatCount = WorkshopRegistration::query()
                    ->where('workshop_session_id', $lockedSession->id)
                    ->where('registration_status', '!=', 'cancelled')
                    ->whereNotNull('seat_number')
                    ->get(['seat_number'])
                    ->sum(function (WorkshopRegistration $registration) {
                        return collect(explode(',', (string) $registration->seat_number))
                            ->filter(fn (string $seat) => trim($seat) !== '')
                            ->count();
                    });

                $maxCapacity = (int) ($lockedSession->max_capacity ?? $lockedSession->max_seats ?? 0);
                if ($maxCapacity > 0 && ($allocatedSeatCount + $ticketCount) > $maxCapacity) {
                    throw new HttpException(422, 'Not enough seats are available for your selected ticket quantity.');
                }

                $startSeatNumber = $allocatedSeatCount + 1;
                $seatCodes = collect(range($startSeatNumber, $startSeatNumber + $ticketCount - 1))
                    ->map(fn (int $seatNumber) => $this->formatSeatCode($lockedSession, $seatNumber));

                $seatNumbers = $seatCodes->implode(',');
                $referenceNumber = $this->generateReferenceNumber(
                    fullName: $validated['full_name'],
                    firstSeatCode: (string) $seatCodes->first(),
                    sessionDate: $lockedSession->session_date->format('dmY')
                );

                $reg = WorkshopRegistration::create([
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

                // Increment session registration count
                $lockedSession->increment('registrations_count');

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

            // Use a signed confirmation URL to prevent enumeration of registration records.
            $confirmationUrl = URL::temporarySignedRoute(
                'registration.confirmation',
                now()->addDays(7),
                ['registration' => $registration->id]
            );

            return redirect()
                ->to($confirmationUrl)
                ->with('success', 'Registration successful! Your reference number is: ' . $registration->reference_number);

        } catch (HttpException $e) {
            if ($e->getStatusCode() === 422) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', $e->getMessage());
            }

            throw $e;

        } catch (QueryException $e) {
            $isUniqueViolation = str_contains(strtolower($e->getMessage()), 'unique')
                || str_contains(strtolower($e->getMessage()), 'duplicate');

            if ($isUniqueViolation) {
                $existingRegistration = WorkshopRegistration::query()
                    ->where('workshop_session_id', $session->id)
                    ->whereRaw('LOWER(email_address) = ?', [strtolower((string) $request->input('email_address'))])
                    ->first();

                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Your email is already registered for this workshop. Reference: ' . ($existingRegistration?->reference_number ?? 'N/A'));
            }

            throw $e;

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

    private function generateReferenceNumber(string $fullName, string $firstSeatCode, string $sessionDate): string
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

    private function formatSeatCode(WorkshopSession $session, int $seatNumber): string
    {
        $sessionCode = Str::padLeft((string) $session->id, 2, '0');
        $dateCode = $session->session_date->format('dmY');

        return "WS{$sessionCode}-{$dateCode}-" . Str::padLeft((string) $seatNumber, 4, '0');
    }

    private function canAcceptRegistrations(string $status): bool
    {
        return $status === 'upcoming';
    }
}