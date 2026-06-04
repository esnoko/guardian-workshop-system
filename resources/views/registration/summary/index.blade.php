<aside class="registration-summary-card">
    <header class="panel-head summary-head">
        <img src="{{ asset('images/Reg_summary.png') }}" alt="Registration summary" class="panel-icon-img">
        <div>
            <h2>Registration Summary</h2>
            <p>Review your details before submitting</p>
        </div>
    </header>

    <div class="summary-row summary-date">
        <div class="summary-date-icon-label">
            <img src="{{ asset('images/calender.png') }}" alt="Calendar" class="summary-date-icon">
            <span>Workshop Date</span>
        </div>
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
                <button type="button" class="ticket-option {{ $count === $selectedTickets ? 'active' : '' }}" data-ticket-count="{{ $count }}" data-ticket-price="{{ $ticketPrice }}" data-subtotal="{{ $ticketPrice * $count }}" data-grand-total="{{ $ticketPrice * $count * 1.15 }}">
                    <span class="ticket-count">{{ $count }}</span>
                    <span class="ticket-avatars">
                        @for($i = 1; $i <= $count; $i++)
                            <svg viewBox="0 0 24 24" fill="currentColor" class="ticket-avatar" aria-hidden="true">
                                <circle cx="12" cy="8" r="4"></circle>
                                <path d="M12 14c-4 0-6 2-6 4v4h12v-4c0-2-2-4-6-4z"></path>
                            </svg>
                        @endfor
                    </span>
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
    <span class="price-label">
        <img src="{{ asset('images/Price_tag.png') }}" alt="Price Tag" class="price-icon">
        Price per ticket (exl vat)
    </span>
    <strong>R{{ number_format($ticketPrice, 2) }}</strong>
</div>

<div class="price-line">
    <span class="price-title">Total Amount (exl vat)</span>
    <strong>R{{ number_format($subtotal, 2) }}</strong>
</div>

<div class="price-line grand">
    <span class="price-title">GrandTotal (incl vat)</span>
    <strong>R{{ number_format($grandTotal, 2) }}</strong>
</div>

</aside>
