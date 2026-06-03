<header class="topbar-wrapper">
    <div class="topbar">
        <div class="topbar-brand-row">
            <a href="{{ route('home') }}" class="brand" aria-label="Tekete Safe Space home">
                <img src="{{ asset('images/Tekete Safe space logo.png') }}" alt="Tekete Safe Space" class="brand-logo">
            </a>
        </div>

        <div class="topbar-nav-row">
            <input id="page-nav-toggle" class="main-navigation-toggle" type="checkbox" aria-label="Toggle navigation" />
            <label for="page-nav-toggle" class="menu-toggle" aria-controls="primary-navigation">
                <svg class="icon--menu-toggle" viewBox="0 0 60 30" aria-hidden="true">
                    <g class="icon-group">
                        <g class="icon--menu">
                            <path d="M 6 0 L 54 0" />
                            <path d="M 6 15 L 54 15" />
                            <path d="M 6 30 L 54 30" />
                        </g>
                        <g class="icon--close">
                            <path d="M 15 0 L 45 30" />
                            <path d="M 15 30 L 45 0" />
                        </g>
                    </g>
                </svg>
                <span class="menu-toggle-label">Menu</span>
            </label>

            <nav id="primary-navigation" class="top-nav" aria-label="Primary navigation menu">
                <a href="{{ route('home') }}">Home</a>
                <a href="#about">About Us</a>
                <a href="{{ route('workshops.index') }}" class="active" aria-current="page">Workshops</a>
                <a href="#contact">Contact Us</a>
            </nav>
        </div>
    </div>
</header>