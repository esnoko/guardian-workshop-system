<?php

namespace App\Providers;

use App\Events\RegistrationCreated;
use App\Listeners\SendRegistrationConfirmationEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RegistrationCreated::class => [
            SendRegistrationConfirmationEmail::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
