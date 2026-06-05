<aside class="registration-summary-card">
    @php
        $currentSelectedTickets = (int) old('ticket_count', $selectedTickets);
    @endphp

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
            {{ \Illuminate\Support\Carbon::parse($session->start_time)->format('h:i A') }} - {{ \Illuminate\Support\Carbon::parse($session->end_time)->format('h:i A') }}
        </strong>
    </div>

    <div class="summary-divider"></div>

    <div class="ticket-block">
        <p>Number of tickets <small>(Max 3 per email)</small></p>
        <div class="ticket-options" role="group" aria-label="Number of tickets">
            @foreach($ticketOptions as $count)
                <button
                    type="button"
                    class="ticket-option {{ $count === $currentSelectedTickets ? 'active' : '' }}"
                    data-ticket-count="{{ $count }}"
                    data-ticket-price="{{ $ticketPrice }}"
                    data-subtotal="{{ $ticketPrice * $count }}"
                    data-grand-total="{{ $ticketPrice * $count * 1.15 }}"
                    data-ticket-number="{{ $ticketNumber }}_{{ $count }}"
                >
                    <span class="ticket-count">{{ $count }}</span>
                    <span class="ticket-avatars">
                        @for($i = 1; $i <= $count; $i++)
                            <svg viewBox="0 0 24 24" class="ticket-avatar" aria-hidden="true">
    <circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="2"></circle>
    <path d="M12 14c-4 0-6 2-6 4v4h12v-4c0-2-2-4-6-4z"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linejoin="round">
    </path>
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
        <strong id="ticketNumberDisplay">{{ $ticketNumber }}_{{ $currentSelectedTickets }}</strong>
    </div>

    <div class="seat-box">
        <p><strong>Seat Numbers</strong> (To be assigned after registration)</p>
        <div id="seatPreviewList">
            @for($i = 1; $i <= $currentSelectedTickets; $i++)
                <div class="seat-line">
                    <span>Seat {{ $i }}</span>
                    <strong>Assigned after registration</strong>
                </div>
            @endfor
        </div>
    </div>

    <div class="price-line">
    <span class="price-label">
       <span class="price-label">
    <svg class="price-icon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M3 12V5h7l11 11-7 7L3 12z"
              fill="none"
              stroke="currentColor"
              stroke-width="2"/>
        <circle cx="8" cy="8" r="1.5" fill="currentColor"/>
    </svg>
    Price per ticket (exl vat)
</span>
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
