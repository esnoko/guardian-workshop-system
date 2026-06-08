<aside class="payment-summary">
    <div class="payment-summary-head">
        <img class="payment-summary-badge" src="{{ asset('images/Reg_summary.png') }}" alt="" aria-hidden="true">
        <div>
            <h3>Registration Summary</h3>
            <p>Review your details before submitting</p>
        </div>
    </div>

    <div class="summary-row summary-row--stacked">
        <span class="summary-label-with-icon">
            <img class="summary-label-icon" src="{{ asset('images/calender.png') }}" alt="" aria-hidden="true">
            <span>Workshop Date</span>
        </span>
        <span class="summary-value summary-value--stacked">
            {{ $session->session_date->format('D, d M Y') }}<br>
            {{ \Illuminate\Support\Carbon::parse($session->start_time)->format('h:i A') }} - {{ \Illuminate\Support\Carbon::parse($session->end_time)->format('h:i A') }}
        </span>
    </div>

    <div class="summary-row summary-row--tickets">
        <span>Number of tickets <small>(Max 3 per email)</small></span>
        <div class="summary-ticket-selected" aria-label="{{ $ticketCount }} ticket{{ $ticketCount > 1 ? 's' : '' }} selected">
            <span class="summary-ticket-avatars" aria-hidden="true">
                @for ($i = 1; $i <= $ticketCount; $i++)
                    <svg viewBox="0 0 24 24" fill="currentColor" class="summary-ticket-avatar" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path d="M12 14c-4 0-6 2-6 4v4h12v-4c0-2-2-4-6-4z"></path>
                    </svg>
                @endfor
            </span>
        </div>
    </div>

    <div class="summary-row">
        <span>Reference Number</span>
        <span class="summary-ticket-number">{{ $registration->reference_number }}</span>
    </div>

    <div class="summary-seats">
        <div class="summary-seats-head">
            <strong>Seat Numbers</strong>
            <span>(To be assigned after registration)</span>
        </div>
        @if ($seatNumbers->isNotEmpty())
            @foreach($seatNumbers as $index => $seat)
                <div class="summary-seat-line">
                    <strong>Seat {{ $index + 1 }}</strong>
                    <span>{{ $seat }}</span>
                </div>
            @endforeach
        @else
            @for ($i = 1; $i <= $ticketCount; $i++)
                <div class="summary-seat-line">
                    <strong>Seat {{ $i }}</strong>
                    <span>Assigned after registration</span>
                </div>
            @endfor
        @endif
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
