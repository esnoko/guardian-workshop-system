<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Workshop Registration Confirmation</title>
    </head>
    <body style="font-family: Arial, sans-serif; color: #101010; line-height: 1.5;">
        <h1 style="font-size: 24px; margin-bottom: 8px;">Registration confirmed</h1>
        <p>Hello {{ $registration->full_name }},</p>
        <p>Your workshop registration has been recorded. Please keep this reference number:</p>
        <p style="font-size: 18px; font-weight: bold;">{{ $registration->reference_number }}</p>

        <h2 style="font-size: 18px; margin-top: 24px;">Workshop details</h2>
        <p>
            {{ $registration->session?->workshop?->title ?? 'Workshop' }}<br>
            {{ $registration->session?->session_date?->format('d M Y') }}<br>
            {{ substr((string) $registration->session?->start_time, 0, 5) }} - {{ substr((string) $registration->session?->end_time, 0, 5) }}
        </p>

        <p>
            <a href="{{ $paymentUrl }}" style="display: inline-block; padding: 10px 14px; background: #c7da30; color: #101010; text-decoration: none; font-weight: bold;">
                Continue to payment
            </a>
        </p>

        <p>
            You can also view your confirmation here:
            <a href="{{ $confirmationUrl }}">{{ $confirmationUrl }}</a>
        </p>
    </body>
</html>
