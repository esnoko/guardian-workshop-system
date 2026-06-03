<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SACE Workshop Registration</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;700;800&display=swap" rel="stylesheet">

        @php
            $baseCssPath = resource_path('views/global/base.css');
            $navbarCssPath = resource_path('views/components/navbar/navbar.css');
            $registrationCssPath = resource_path('views/registration/registration.css');
        @endphp

        @if (file_exists($baseCssPath))
            <style>{!! file_get_contents($baseCssPath) !!}</style>
        @endif

        @if (file_exists($navbarCssPath))
            <style>{!! file_get_contents($navbarCssPath) !!}</style>
        @endif

        @if (file_exists($registrationCssPath))
            <style>{!! file_get_contents($registrationCssPath) !!}</style>
        @endif
    </head>
    <body>
        <main class="page-shell registration-page">
            @include('components.navbar.index')

            <div class="registration-wrap">
                <a href="{{ route('workshops.index') }}" class="back-link">&larr; Back to Workshop Schudule</a>

                <h1 class="registration-title">SACE Workshop Registration</h1>

                <section class="registration-grid">
                    <article class="registration-form-card">
                        <header class="panel-head">
                            <span class="panel-icon" aria-hidden="true">&#128100;</span>
                            <div>
                                <h2>Attendee Information</h2>
                                <p>Please fill in your details below</p>
                            </div>
                        </header>

                        <form class="registration-form" action="#" method="post">
                            @csrf

                            <label>
                                Full Name<span class="required">*</span>
                                <input type="text" name="full_name" placeholder="Enter full name and surname" required>
                            </label>

                            <label>
                                School Name<span class="required">*</span>
                                <input type="text" name="school_name" placeholder="Enter your school name" required>
                            </label>

                            <label>
                                Email Address<span class="required">*</span>
                                <input type="email" name="email_address" placeholder="Enter your email address" required>
                            </label>

                            <label>
                                Phone Number<span class="required">*</span>
                                <input type="text" name="phone_number" placeholder="Enter your phone number" required>
                            </label>

                            <div class="split-fields">
                                <label>
                                    Province / Region<span class="required">*</span>
                                    <select name="province_region" required>
                                        <option value="">Select Province/Region</option>
                                        <option>Gauteng</option>
                                        <option>Western Cape</option>
                                        <option>KwaZulu-Natal</option>
                                    </select>
                                </label>

                                <label>
                                    District<span class="required">*</span>
                                    <select name="district" required>
                                        <option value="">Select District</option>
                                        <option>Johannesburg North</option>
                                        <option>Johannesburg South</option>
                                        <option>Ekurhuleni</option>
                                    </select>
                                </label>
                            </div>

                            <label>
                                Position / Role
                                <input type="text" name="position_role" placeholder="Enter your position / Role">
                            </label>
                        </form>
                    </article>

                    <aside class="registration-summary-card">
                        <header class="panel-head summary-head">
                            <span class="panel-icon" aria-hidden="true">&#128221;</span>
                            <div>
                                <h2>Registration Summary</h2>
                                <p>Review your details before submitting</p>
                            </div>
                        </header>

                        <div class="summary-row summary-date">
                            <span>Workshop Date</span>
                            <strong>
                                {{ $session->session_date->format('D, d M Y') }}<br>
                                {{ \Illuminate\Support\Carbon::parse($session->start_time)->format('H:i A') }} - {{ \Illuminate\Support\Carbon::parse($session->end_time)->format('H:i A') }}
                            </strong>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="ticket-block">
                            <p>Number of tickets <small>(Max 3 per email)</small></p>
                            <div class="ticket-options" role="group" aria-label="Number of tickets">
                                @foreach($ticketOptions as $count)
                                    <button type="button" class="ticket-option {{ $count === $selectedTickets ? 'active' : '' }}">
                                        @if($count === 3)
                                            {{ $count }} &#128100; &#128100; &#128100;
                                        @elseif($count === 2)
                                            {{ $count }} &#128100; &#128100;
                                        @else
                                            {{ $count }} &#128100;
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="ticket-number-row">
                            <span>Ticket Number</span>
                            <strong>{{ $ticketNumber }}</strong>
                        </div>

                        <div class="seat-box">
                            <p><strong>Seat Numbers</strong> (To be assigned after registration)</p>
                            @foreach($seatNumbers as $seatLabel => $seatValue)
                                <div class="seat-line">
                                    <span>{{ $seatLabel }}</span>
                                    <strong>{{ $seatValue }}</strong>
                                </div>
                            @endforeach
                        </div>

                        <div class="price-line">
                            <span>Price per ticket (exl vat)</span>
                            <strong>R{{ number_format($ticketPrice, 2) }}</strong>
                        </div>
                        <div class="price-line">
                            <span>Total Amount (exl vat)</span>
                            <strong>R{{ number_format($subtotal, 2) }}</strong>
                        </div>
                        <div class="price-line grand">
                            <span>GrandTotal (incl vat)</span>
                            <strong>R{{ number_format($grandTotal, 2) }}</strong>
                        </div>
                    </aside>
                </section>

                <div class="continue-wrap">
                    <button type="button" class="continue-btn">Continue</button>
                </div>

                <section class="registration-notes">
                    <div class="note-row">
                        <span class="note-icon" aria-hidden="true">&#128274;</span>
                        <p>
                            The SACE-endorsed short course, Guardian of Privacy: Digital Ethics and Mandatory Reporting within the Tekete Safe Space APP, equips educators and school stakeholders with practical knowledge on digital ethics, learner confidentiality, safeguarding responsibilities, and mandatory reporting within educational environments.
                        </p>
                    </div>

                    <div class="note-row">
                        <span class="note-icon" aria-hidden="true">&#128101;</span>
                        <p>
                            Participants will earn 5 CPTD points upon successful completion of the course. The programme is designed to strengthen ethical reporting practices, promote learner safety, and support schools in navigating challenges related to bullying, abuse reporting, and digital wellbeing.
                        </p>
                    </div>

                    <aside class="registration-sace" aria-label="SACE badge">
                        <div class="sace-top" aria-hidden="true"></div>
                        <strong>SACE</strong>
                        <small>South African Council for Educators</small>
                    </aside>
                </section>
            </div>

            <footer class="page-footer">&copy; 2026 Tekete Safe Space From Moepi Publishing. All rights reserved.</footer>
        </main>
    </body>
</html>
