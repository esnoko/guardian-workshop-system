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
