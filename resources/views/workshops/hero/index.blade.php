<section class="hero">
    <div class="hero-copy">
        <h1>SACE Workshop <span class="hero-title-accent">Schedule</span></h1>

        @if($workshop)
            <div class="price-pill" aria-label="Workshop price per session">
                <span class="label">
                    <span class="price-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 4H20V12L11.2 20.8C10.7 21.3 9.85 21.3 9.35 20.8L3.2 14.65C2.7 14.15 2.7 13.3 3.2 12.8L12 4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <circle cx="16.7" cy="7.3" r="1.4" fill="currentColor"/>
                        </svg>
                    </span>
                    Price per session:
                </span>
                <span class="value">R{{ number_format((float) $workshop->fee, 0) }}</span>
            </div>

            <div class="course-block">
                <p class="course-label">Course:</p>
                <p class="course-title">Guardian of Privacy:</p>
                <p class="hero-summary">Digital Ethics and Mandatory Reporting within the Tekete Safe Space.</p>
            </div>
        @endif
    </div>

<aside class="hero-visual" aria-label="Workshop overview image">
    <img
        src="{{ asset('images/ChatGPT Image May 22, 2026, 09_41_21 AM (1).png') }}"
        alt="Workshop Overview"
        class="hero-image">
</aside>
</section>