<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SACE Workshop Registration</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;700;800&display=swap" rel="stylesheet">

        @php
            $baseCssPath = resource_path('views/global/base.css');
            $navbarCssPath = resource_path('views/components/navbar/navbar.css');
            $endorsementCssPath = resource_path('views/components/endorsement/endorsement.css');
            $headerCssPath = resource_path('views/registration/header/header.css');
            $formCssPath = resource_path('views/registration/form/form.css');
            $summaryCssPath = resource_path('views/registration/summary/summary.css');
            $continueCssPath = resource_path('views/registration/continue/continue.css');
        @endphp

        @if (file_exists($baseCssPath))
            <style>{!! file_get_contents($baseCssPath) !!}</style>
        @endif

        @if (file_exists($navbarCssPath))
            <style>{!! file_get_contents($navbarCssPath) !!}</style>
        @endif

        @if (file_exists($headerCssPath))
            <style>{!! file_get_contents($headerCssPath) !!}</style>
        @endif

        @if (file_exists($formCssPath))
            <style>{!! file_get_contents($formCssPath) !!}</style>
        @endif

        @if (file_exists($summaryCssPath))
            <style>{!! file_get_contents($summaryCssPath) !!}</style>
        @endif

        @if (file_exists($continueCssPath))
            <style>{!! file_get_contents($continueCssPath) !!}</style>
        @endif

        @if (file_exists($endorsementCssPath))
            <style>{!! file_get_contents($endorsementCssPath) !!}</style>
        @endif
    </head>
    <body>
        <main class="page-shell registration-page">
            @include('components.navbar.index')

            <div class="registration-wrap">
                @include('registration.header.index')

                @if (session('error'))
                    <div class="flash-message flash-error">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                @if (session('success'))
                    <div class="flash-message flash-success">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                <form class="registration-form-wrapper" action="{{ route('registrations.store', ['session' => $session->id]) }}" method="post" id="registrationForm">
                    @csrf

                    <section class="registration-grid">
                        @include('registration.form.index')
                        @include('registration.summary.index')
                    </section>

                    @include('registration.continue.index')
                </form>
            </div>

            @include('components.endorsement.index')
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ticketOptions = document.querySelectorAll('.ticket-option');
                const ticketCountInput = document.getElementById('ticketCountInput');
                const subtotalElement = document.querySelector('.price-line:nth-child(6) strong');
                const grandTotalElement = document.querySelector('.price-line.grand strong');
                const ticketNumberDisplay = document.getElementById('ticketNumberDisplay');
                const seatPreviewList = document.getElementById('seatPreviewList');

                function renderSeatPreview(ticketCount) {
                    if (!seatPreviewList) {
                        return;
                    }

                    seatPreviewList.innerHTML = '';

                    for (let i = 1; i <= ticketCount; i += 1) {
                        const row = document.createElement('div');
                        row.className = 'seat-line';

                        const label = document.createElement('span');
                        label.textContent = `Seat ${i}`;

                        const value = document.createElement('strong');
                        value.textContent = 'Assigned after registration';

                        row.appendChild(label);
                        row.appendChild(value);
                        seatPreviewList.appendChild(row);
                    }
                }

                function applyTicketSelection(button) {
                    const ticketCount = parseInt(button.dataset.ticketCount, 10);

                    // Update hidden input for form submission
                    if (ticketCountInput) {
                        ticketCountInput.value = String(ticketCount);
                    }

                    // Update ticket number preview
                    if (ticketNumberDisplay && button.dataset.ticketNumber) {
                        ticketNumberDisplay.textContent = button.dataset.ticketNumber;
                    }

                    // Update seat lines preview
                    if (!Number.isNaN(ticketCount)) {
                        renderSeatPreview(ticketCount);
                    }

                    // Update totals
                    const subtotal = parseFloat(button.dataset.subtotal);
                    const grandTotal = parseFloat(button.dataset.grandTotal);

                    if (subtotalElement && grandTotalElement) {
                        subtotalElement.textContent = 'R' + subtotal.toFixed(2);
                        grandTotalElement.textContent = 'R' + grandTotal.toFixed(2);
                    }
                }

                ticketOptions.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Remove active class from all buttons
                        ticketOptions.forEach(btn => btn.classList.remove('active'));
                        
                        // Add active class to clicked button
                        this.classList.add('active');

                        applyTicketSelection(this);
                    });
                });

                // Ensure preview aligns with current selected state on initial page render.
                const activeButton = document.querySelector('.ticket-option.active');
                if (activeButton) {
                    applyTicketSelection(activeButton);
                }
            });
        </script>
    </body>
</html>
