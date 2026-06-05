<section class="hero">
    <div class="hero-copy">
        <h1>SACE Workshop <span class="hero-title-accent">Schedule</span></h1>

        @if($workshop)
            <div class="price-pill" aria-label="Workshop price per session">
                <span class="label">
                    <span class="price-icon">
    <img
        src="{{ asset('images/Price_tag.png') }}"
        alt=""
        class="price-icon-image">
</span>
                    Price per session:
                </span>
                <span class="value">R{{ (int) $workshop->fee }}</span>
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