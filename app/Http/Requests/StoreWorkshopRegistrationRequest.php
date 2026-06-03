<?php

namespace App\Http\Requests;

use App\Models\WorkshopSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkshopRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'school_name' => ['required', 'string', 'max:255'],
            'email_address' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'regex:/^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/'],
            'province_region' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'position_role' => ['required', 'string', 'max:255'],
            'ticket_count' => ['required', 'integer', 'min:1', 'max:' . config('workshops.registration.max_tickets_per_registration')],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required.',
            'school_name.required' => 'School name is required.',
            'email_address.required' => 'Email address is required.',
            'email_address.email' => 'Please enter a valid email address.',
            'phone_number.required' => 'Phone number is required.',
            'phone_number.regex' => 'Please enter a valid phone number format.',
            'province_region.required' => 'Province/Region is required.',
            'district.required' => 'District is required.',
            'position_role.required' => 'Position/Role is required.',
            'ticket_count.required' => 'Please select number of tickets.',
            'ticket_count.min' => 'At least 1 ticket required.',
            'ticket_count.max' => 'Maximum ' . config('workshops.registration.max_tickets_per_registration') . ' tickets allowed per registration.',
        ];
    }
}
