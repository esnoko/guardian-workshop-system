<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Payment</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;700;800&display=swap" rel="stylesheet">

        @php
            $baseCssPath = resource_path('views/global/base.css');
            $navbarCssPath = resource_path('views/components/navbar/navbar.css');
            $summaryCssPath = resource_path('views/registration/summary/summary.css');
        @endphp

        @if (file_exists($baseCssPath))
            <style>{!! file_get_contents($baseCssPath) !!}</style>
        @endif

        @if (file_exists($navbarCssPath))
            <style>{!! file_get_contents($navbarCssPath) !!}</style>
        @endif

        @if (file_exists($summaryCssPath))
            <style>{!! file_get_contents($summaryCssPath) !!}</style>
        @endif

        @php $paymentCssPath = resource_path('views/payment/payment.css'); @endphp
        @if (file_exists($paymentCssPath))
            <style>{!! file_get_contents($paymentCssPath) !!}</style>
        @endif
    </head>
    <body>
        <main class="page-shell payment-page">
            @include('components.navbar.index')

            <div class="payment-wrap">
                <header class="payment-header">
                    <a href="{{ route('workshops.register', ['session' => $session->id]) }}" class="payment-back">
                        <span class="payment-back-arrow">←</span>
                        <span class="payment-back-label">Back to Workshop Registration</span>
                    </a>
                    <h1 class="payment-title">Payment</h1>
                    <p class="payment-subtitle">Complete your payment to complete your workshop booking</p>
                </header>

                @if (session('error'))
                    <div class="flash-message flash-error">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <section class="payment-grid">
                    <div class="payment-options">
                        <form class="payment-card" method="post" action="{{ \Illuminate\Support\Facades\URL::signedRoute('payment.initiate', ['registration' => $registration->id]) }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="payfast">
                            <input type="hidden" name="payment_plan" value="full">
                            <div class="payment-card-head">
                                <span class="payment-card-badge payment-card-badge--payfast" aria-hidden="true"></span>
                                <div class="payment-card-brand">
                                    <h2>PayFast</h2>
                                    <p>Pay securely via PayFast</p>
                                </div>
                            </div>
                            <ul class="payment-list">
                                <li><span class="payment-list-icon" aria-hidden="true"></span>Instant eft</li>
                                <li><span class="payment-list-icon" aria-hidden="true"></span>Credit &amp; Debit Card</li>
                                <li><span class="payment-list-icon" aria-hidden="true"></span>Secure and trusted payments</li>
                            </ul>
                            <button class="payment-action" type="submit">Continue with PayFast</button>
                        </form>

                        <form class="payment-card" method="post" action="{{ \Illuminate\Support\Facades\URL::signedRoute('payment.initiate', ['registration' => $registration->id]) }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="payflex">
                            <input type="hidden" name="payment_plan" value="installment">
                            <div class="payment-card-head">
                                <span class="payment-card-badge payment-card-badge--payflex" aria-hidden="true"></span>
                                <div class="payment-card-brand">
                                    <h2>payflex</h2>
                                    <p>Get it now, pay later with payflex</p>
                                </div>
                            </div>
                            <ul class="payment-list">
                                <li><span class="payment-list-icon" aria-hidden="true"></span>Pay in 3 interest-free payments</li>
                                <li><span class="payment-list-icon" aria-hidden="true"></span>No fees when you pay on time</li>
                                <li><span class="payment-list-icon" aria-hidden="true"></span>Quick and easy application</li>
                            </ul>
                            <p class="payment-note">Certificate will be withheld until payment is complete</p>
                            <button class="payment-action" type="submit">Continue with PayFlex</button>
                        </form>
                    </div>

                    <aside class="payment-summary">
                        <div class="payment-summary-head">
                            <span class="payment-summary-badge" aria-hidden="true"></span>
                            <div>
                                <h3>Registration Summary</h3>
                                <p>Review your details before submitting</p>
                            </div>
                        </div>

                        <div class="summary-row summary-row--stacked">
                            <span>Workshop Date</span>
                            <span class="summary-value summary-value--stacked">
                                {{ $session->session_date->format('D, d M Y') }}<br>
                                {{ \Illuminate\Support\Carbon::parse($session->start_time)->format('h:i A') }} - {{ \Illuminate\Support\Carbon::parse($session->end_time)->format('h:i A') }}
                            </span>
                        </div>

                        <div class="summary-row summary-row--tickets">
                            <span>Number of tickets <small>(Max 3 per email)</small></span>
                            <div class="summary-ticket-list" aria-label="Selected ticket count">
                                @for ($i = 1; $i <= 3; $i++)
                                    <span class="summary-ticket-pill {{ $i <= $ticketCount ? 'is-active' : '' }}">{{ $i }}</span>
                                @endfor
                            </div>
                        </div>

                        <div class="summary-row">
                            <span>Ticket Number</span>
                            <span class="summary-ticket-number">{{ $registration->session->session_date->format('dmY') }}_{{ str_pad((string) $registration->session->id, 2, '0', STR_PAD_LEFT) }}_{{ $ticketCount }}</span>
                        </div>

                        <div class="summary-seats">
                            <div class="summary-seats-head">
                                <strong>Seat Numbers</strong>
                                <span>(To be assigned after registration)</span>
                            </div>
                            @foreach($seatNumbers as $index => $seat)
                                <div class="summary-seat-line">
                                    <strong>Seat {{ $index + 1 }}</strong>
                                    <span>{{ $seat }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="summary-pricing">
                            <div class="summary-pricing-row">
                                <span>Price per ticket (excl vat)</span>
                                <strong>R{{ number_format((float) $workshop->fee, 2) }}</strong>
                            </div>
                            <div class="summary-pricing-row">
                                <span>Total Amount (excl vat)</span>
                                <strong>R{{ number_format((float) $workshop->fee * $ticketCount, 2) }}</strong>
                            </div>
                            <div class="summary-pricing-row total">
                                <span>Grand Total (incl vat)</span>
                                <strong>R{{ number_format((float) $registration->amount_due, 2) }}</strong>
                            </div>
                        </div>
                    </aside>
                </section>
            </div>

            <footer class="footer-strip">
                <p>© 2026 Tekete Safe Space From Moepi Publishing. All rights reserved.</p>
            </footer>
        </main>
    </body>
</html>
