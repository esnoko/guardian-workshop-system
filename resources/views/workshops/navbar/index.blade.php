<div class="topbar">
    <div class="topbar-brand-row">
        <a href="{{ route('home') }}" class="brand" aria-label="Tekete Safe Space home">
            <img src="{{ asset('images/Tekete Safe space logo.png') }}" alt="Tekete Safe Space" class="brand-logo">
        </a>
    </div>

    <div class="topbar-nav-row">
        <nav class="top-nav" aria-label="Primary">
            <a href="{{ route('home') }}">Home</a>
            <a href="#about">About Us</a>
            <a href="{{ route('workshops.index') }}" class="active" aria-current="page">Workshops</a>
            <a href="#contact">Contact Us</a>
        </nav>
    </div>
</div>