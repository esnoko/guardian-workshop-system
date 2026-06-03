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
