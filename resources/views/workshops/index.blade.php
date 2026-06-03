<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Guardian Workshop Schedule</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;700;800&display=swap" rel="stylesheet">

        @php
            $baseCssPath = resource_path('views/global/base.css');
            $navbarCssPath = resource_path('views/components/navbar/navbar.css');
            $heroCssPath = resource_path('views/workshops/hero/hero.css');
            $calendarCssPath = resource_path('views/workshops/calendar/calendar.css');
            $endorsementCssPath = resource_path('views/components/endorsement/endorsement.css');
        @endphp

        @if (file_exists($baseCssPath))
            <style>{!! file_get_contents($baseCssPath) !!}</style>
        @endif

        @if (file_exists($navbarCssPath))
            <style>{!! file_get_contents($navbarCssPath) !!}</style>
        @endif

        @if (file_exists($heroCssPath))
            <style>{!! file_get_contents($heroCssPath) !!}</style>
        @endif

        @if (file_exists($calendarCssPath))
            <style>{!! file_get_contents($calendarCssPath) !!}</style>
        @endif

        @if (file_exists($endorsementCssPath))
            <style>{!! file_get_contents($endorsementCssPath) !!}</style>
        @endif

    </head>
    <body>
        <main class="page-shell">
            @php
                $workshop = $workshops->first();
                $sessionsByMonth = $workshop
                    ? $workshop->sessions->groupBy(fn ($session) => $session->session_date->format('F Y'))
                    : collect();
            @endphp

            @include('components.navbar.index')
            @include('workshops.hero.index')
            @include('workshops.calendar.index')
            @include('components.endorsement.index')
        </main>
    </body>
</html>
