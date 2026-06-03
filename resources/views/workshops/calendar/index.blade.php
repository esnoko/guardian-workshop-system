<section class="section-shell">
    @if($workshop && $sessionsByMonth->isNotEmpty())
        @foreach($sessionsByMonth as $month => $sessions)
            <div class="month-row">
                <h2 class="month-title">{{ $month }}</h2>
                <div class="month-pill">
                    <span class="month-icon" aria-hidden="true">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                            <path d="M3 10H21" stroke="currentColor" stroke-width="2"/>
                            <path d="M8 3V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M16 3V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
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
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M3 10H21" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M8 3V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M16 3V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </span>

                            <div class="session-meta">
                                <p class="session-date">{{ $session->session_date->format('D, d M Y') }}</p>
                                <p class="session-time">
                                    {{ \Illuminate\Support\Carbon::parse($session->start_time)->format('h:i A') }} - {{ \Illuminate\Support\Carbon::parse($session->end_time)->format('h:i A') }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('workshops.register', $session) }}" class="book-btn">Book Now</a>
                    </article>
                @endforeach
            </div>
        @endforeach
    @endif
</section>
