<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Payment</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;700;800&display=swap" rel="stylesheet">

        @php
            $baseCssPath = resource_path('views/global/base.css');
            $navbarCssPath = resource_path('views/components/navbar/navbar.css');
            $paymentPageCssPath = resource_path('views/payment/page/page.css');
            $paymentHeaderCssPath = resource_path('views/payment/header/header.css');
            $paymentOptionsCssPath = resource_path('views/payment/options/options.css');
            $paymentSummaryCssPath = resource_path('views/payment/summary/summary.css');
        @endphp

        @if (file_exists($baseCssPath))
            <style>{!! file_get_contents($baseCssPath) !!}</style>
        @endif

        @if (file_exists($navbarCssPath))
            <style>{!! file_get_contents($navbarCssPath) !!}</style>
        @endif

        @if (file_exists($paymentPageCssPath))
            <style>{!! file_get_contents($paymentPageCssPath) !!}</style>
        @endif

        @if (file_exists($paymentHeaderCssPath))
            <style>{!! file_get_contents($paymentHeaderCssPath) !!}</style>
        @endif

        @if (file_exists($paymentOptionsCssPath))
            <style>{!! file_get_contents($paymentOptionsCssPath) !!}</style>
        @endif

        @if (file_exists($paymentSummaryCssPath))
            <style>{!! file_get_contents($paymentSummaryCssPath) !!}</style>
        @endif
    </head>
    <body>
        <main class="page-shell payment-page">
            @include('components.navbar.index')

            <div class="payment-wrap">
                @include('payment.header.index')

                @if (session('error'))
                    <div class="flash-message flash-error">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <section class="payment-grid">
                    @include('payment.options.index')
                    @include('payment.summary.index')
                </section>
            </div>

            <footer class="footer-strip">
                <p>© 2026 Tekete Safe Space From Moepi Publishing. All rights reserved.</p>
            </footer>
        </main>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var cards = document.querySelectorAll('.payment-card');
                var radios = document.querySelectorAll('.payment-method-input');

                function syncSelectedCard() {
                    cards.forEach(function (card) {
                        card.classList.remove('is-selected');
                    });

                    radios.forEach(function (radio) {
                        if (radio.checked) {
                            var card = radio.closest('.payment-card');
                            if (card) {
                                card.classList.add('is-selected');
                            }
                        }
                    });
                }

                radios.forEach(function (radio) {
                    radio.addEventListener('change', syncSelectedCard);
                });

                syncSelectedCard();
            });
        </script>
    </body>
</html>
