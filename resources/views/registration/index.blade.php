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

                <section class="registration-grid">
                    @include('registration.form.index')
                    @include('registration.summary.index')
                </section>

                @include('registration.continue.index')
            </div>

            @include('components.endorsement.index')
        </main>
    </body>
</html>
