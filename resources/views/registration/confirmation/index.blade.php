<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Registration Confirmation</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;700;800&display=swap" rel="stylesheet">

        @php
            $baseCssPath = resource_path('views/global/base.css');
            $navbarCssPath = resource_path('views/components/navbar/navbar.css');
        @endphp

        @if (file_exists($baseCssPath))
            <style>{!! file_get_contents($baseCssPath) !!}</style>
        @endif

        @if (file_exists($navbarCssPath))
            <style>{!! file_get_contents($navbarCssPath) !!}</style>
        @endif

        <style>
            .confirmation-page {
                background: var(--page);
                min-height: 100vh;
            }

            .confirmation-page .topbar {
                padding: 0 3.8rem;
            }

            .confirmation-page .brand-logo {
                height: 142px;
            }

            .confirmation-wrap {
                max-width: 960px;
                margin: 0 auto;
                padding: 1rem 1.25rem 3rem;
            }

            .confirmation-panel {
                border: 1px solid var(--line);
                border-radius: 8px;
                padding: 1.5rem;
                background: #fff;
            }

            .confirmation-kicker {
                margin: 0 0 0.4rem;
                color: var(--lime-dark);
                font-weight: 800;
                text-transform: uppercase;
                font-size: 0.78rem;
            }

            .confirmation-title {
                margin: 0;
                font-family: "Sora", "Manrope", sans-serif;
                font-size: clamp(1.8rem, 4vw, 3rem);
                line-height: 1.08;
            }

            .confirmation-copy {
                color: var(--muted);
                margin: 0.75rem 0 1.25rem;
                max-width: 64ch;
            }

            .confirmation-reference {
                display: inline-flex;
                align-items: center;
                min-height: 44px;
                padding: 0.55rem 0.85rem;
                border-radius: 8px;
                background: #f5fad7;
                font-weight: 800;
                color: var(--text);
                overflow-wrap: anywhere;
            }

            .confirmation-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
                margin-top: 1.25rem;
            }

            .confirmation-section {
                border-top: 1px solid #e6e6e6;
                padding-top: 1rem;
            }

            .confirmation-section h2 {
                margin: 0 0 0.75rem;
                font-size: 1rem;
                font-weight: 800;
            }

            .confirmation-row {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.45rem 0;
                color: var(--muted);
            }

            .confirmation-row strong {
                color: var(--text);
                text-align: right;
            }

            .confirmation-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
                margin-top: 1.5rem;
            }

            .confirmation-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 44px;
                padding: 0.65rem 1rem;
                border-radius: 8px;
                border: 1px solid var(--lime-dark);
                background: var(--lime);
                color: var(--text);
                text-decoration: none;
                font-weight: 800;
            }

            .confirmation-button--secondary {
                background: #fff;
            }

            @media (max-width: 760px) {
                .confirmation-page .topbar {
                    padding: 0 1rem;
                }

                .confirmation-grid {
                    grid-template-columns: 1fr;
                }

                .confirmation-panel {
                    padding: 1rem;
                }
            }
        </style>
    </head>
    <body>
        <main class="page-shell confirmation-page">
            @include('components.navbar.index')

            <div class="confirmation-wrap">
                <section class="confirmation-panel">
                    <p class="confirmation-kicker">Registration confirmed</p>
                    <h1 class="confirmation-title">Your workshop booking is recorded.</h1>
                    <p class="confirmation-copy">
                        Keep this reference number for payment queries, attendance checks, and support.
                    </p>

                    <div class="confirmation-reference">{{ $registration->reference_number }}</div>

                    <div class="confirmation-grid">
                        <section class="confirmation-section">
                            <h2>Workshop</h2>
                            <div class="confirmation-row">
                                <span>Course</span>
                                <strong>{{ $workshop?->title ?? 'Workshop' }}</strong>
                            </div>
                            <div class="confirmation-row">
                                <span>Date</span>
                                <strong>{{ $session?->session_date?->format('d M Y') }}</strong>
                            </div>
                            <div class="confirmation-row">
                                <span>Time</span>
                                <strong>{{ substr((string) $session?->start_time, 0, 5) }} - {{ substr((string) $session?->end_time, 0, 5) }}</strong>
                            </div>
                        </section>

                        <section class="confirmation-section">
                            <h2>Booking</h2>
                            <div class="confirmation-row">
                                <span>Name</span>
                                <strong>{{ $registration->full_name }}</strong>
                            </div>
                            <div class="confirmation-row">
                                <span>Email</span>
                                <strong>{{ $registration->email_address }}</strong>
                            </div>
                            <div class="confirmation-row">
                                <span>Seats</span>
                                <strong>{{ $seatNumbers->implode(', ') }}</strong>
                            </div>
                            <div class="confirmation-row">
                                <span>Status</span>
                                <strong>{{ str_replace('_', ' ', ucfirst($registration->registration_status)) }}</strong>
                            </div>
                        </section>
                    </div>

                    <div class="confirmation-actions">
                        <a class="confirmation-button" href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('payment.start', now()->addDays(7), ['registration' => $registration->id]) }}">
                            Continue to Payment
                        </a>
                        <a class="confirmation-button confirmation-button--secondary" href="{{ route('workshops.index') }}">
                            Back to Workshops
                        </a>
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>
