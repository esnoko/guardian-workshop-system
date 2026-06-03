<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Registration Confirmation</title>

        @php
            $baseCssPath = resource_path('views/global/base.css');
            $headerCssPath = resource_path('views/registration/header/header.css');
            $continueCssPath = resource_path('views/registration/continue/continue.css');
        @endphp

        @if (file_exists($baseCssPath))
            <style>{!! file_get_contents($baseCssPath) !!}</style>
        @endif

        @if (file_exists($headerCssPath))
            <style>{!! file_get_contents($headerCssPath) !!}</style>
        @endif

        @if (file_exists($continueCssPath))
            <style>{!! file_get_contents($continueCssPath) !!}</style>
        @endif

        <style>
            .confirmation-shell {
                max-width: 900px;
                margin: 0 auto;
                padding: 2rem 1.5rem 3rem;
            }

            .confirmation-card {
                background: #fff;
                border: 1px solid var(--line);
                border-radius: 0.5rem;
                padding: 1.5rem;
                box-shadow: 0 10px 28px rgba(0, 0, 0, 0.06);
            }

            .confirmation-grid {
                display: grid;
                gap: 1rem;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                margin-top: 1rem;
            }

            .confirmation-item {
                border: 1px solid #e4e4e4;
                border-radius: 0.4rem;
                padding: 0.9rem;
                background: #fafafa;
            }

            .confirmation-item span {
                display: block;
                font-size: 0.82rem;
                color: var(--muted);
                margin-bottom: 0.25rem;
            }

            .confirmation-item strong {
                font-size: 1rem;
                color: var(--text);
            }

            .confirmation-actions {
                display: flex;
                gap: 0.75rem;
                margin-top: 1.5rem;
                flex-wrap: wrap;
            }

            .confirmation-link {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.7rem 1.1rem;
                border-radius: 999px;
                border: 2px solid var(--line);
                color: var(--button-text);
                text-decoration: none;
                font-weight: 700;
            }

            @media (max-width: 720px) {
                .confirmation-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <main class="confirmation-shell page-shell">
            <div class="confirmation-card">
                <h1 class="registration-title">Registration Confirmed</h1>
                <p>Your registration has been saved successfully. Keep the reference details below for payment and support.</p>

                @if (session('success'))
                    <div class="flash-message flash-success">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                <div class="confirmation-grid">
                    <div class="confirmation-item">
                        <span>Reference Number</span>
                        <strong>{{ $registration->reference_number }}</strong>
                    </div>
                    <div class="confirmation-item">
                        <span>Registration Status</span>
                        <strong>{{ ucfirst($registration->registration_status) }}</strong>
                    </div>
                    <div class="confirmation-item">
                        <span>Full Name</span>
                        <strong>{{ $registration->full_name }}</strong>
                    </div>
                    <div class="confirmation-item">
                        <span>Email Address</span>
                        <strong>{{ $registration->email_address }}</strong>
                    </div>
                    <div class="confirmation-item">
                        <span>Workshop Session</span>
                        <strong>{{ $workshop?->title ?? 'Workshop' }}</strong>
                    </div>
                    <div class="confirmation-item">
                        <span>Amount Due</span>
                        <strong>R{{ number_format((float) $registration->amount_due, 2) }}</strong>
                    </div>
                    <div class="confirmation-item">
                        <span>Seat Numbers</span>
                        <strong>{{ $seatNumbers->implode(', ') ?: 'Pending assignment' }}</strong>
                    </div>
                    <div class="confirmation-item">
                        <span>Session Date</span>
                        <strong>{{ $session?->session_date?->format('D, d M Y') }}</strong>
                    </div>
                </div>

                <div class="confirmation-actions">
                    <a href="{{ route('workshops.index') }}" class="confirmation-link">Back to Workshops</a>
                    <a href="{{ route('workshops.register', ['session' => $session->id]) }}" class="confirmation-link">Register Another Participant</a>
                </div>
            </div>
        </main>
    </body>
</html>