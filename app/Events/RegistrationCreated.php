<?php

namespace App\Events;

use App\Models\WorkshopRegistration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegistrationCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WorkshopRegistration $registration
    ) {}
}
