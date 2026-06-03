<section class="section-shell">
    @if($workshop && $sessionsByMonth->isNotEmpty())
        @foreach($sessionsByMonth as $month => $sessions)
            <div class="month-row">
                <h2 class="month-title">{{ $month }}</h2>
                <div class="month-pill">
                    <span class="month-icon" aria-hidden="true">
                        <img src="{{ asset('images/3.png') }}" alt="Calendar Month Badge" class="calendar-icon-img">
                    </span>
                    <span>{{ strtoupper($month) }}</span>
                </div>
                <div class="month-line" aria-hidden="true"></div>
            </div>

            <div class="sessions-grid">
                @foreach($sessions as $session)
                    <article class="session-card">
                        <div class="session-head">
                            <span class="session-cal" aria-hidden="true">
                                <img src="{{ asset('images/4.png') }}" alt="Session Date Icon" class="calendar-icon-img">
                            </span>

                            <div class="session-meta">
                                <p class="session-date">{{ $session->session_date->format('D, d M Y') }}</p>
                                <p class="session-time">
                                    {{ \Illuminate\Support\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($session->end_time)->format('H:i') }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('workshops.register', $session) }}" class="book-btn-link">
                            <button class="book-btn">Book Now</button>
                        </a>
                    </article>
                @endforeach
            </div>
        @endforeach
    @endif
</section>