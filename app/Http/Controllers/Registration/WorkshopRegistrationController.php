<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkshopRegistrationRequest;
use App\Models\WorkshopRegistration;
use App\Models\WorkshopSession;
use App\Services\Registration\RegistrationResponseBuilder;
use App\Services\Registration\WorkshopRegistrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorkshopRegistrationController extends Controller
{
    public function __construct(
        private readonly WorkshopRegistrationService $registrationService,
        private readonly RegistrationResponseBuilder $responseBuilder,
    ) {
    }

    public function create(WorkshopSession $session): View
    {
        if (!$this->registrationService->canAcceptRegistrations($session)) {
            throw new HttpException(400, 'This workshop session is not currently accepting registrations.');
        }

        return view('registration.index', [
            'session' => $session,
            ...$this->responseBuilder->build($session),
        ]);
    }

    public function store(StoreWorkshopRegistrationRequest $request, WorkshopSession $session): RedirectResponse
    {
        try {
            if (!$this->registrationService->canAcceptRegistrations($session)) {
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
            $registration = $this->registrationService->createRegistration($session, $validated);

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
}
