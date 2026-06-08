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
            'additional_attendees' => ['nullable', 'array'],
            'additional_attendees.*.full_name' => ['nullable', 'string', 'max:255'],
            'additional_attendees.*.school_name' => ['nullable', 'string', 'max:255'],
            'additional_attendees.*.phone_number' => ['nullable', 'string', 'regex:/^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/'],
            'additional_attendees.*.province_region' => ['nullable', 'string', 'max:255'],
            'additional_attendees.*.district' => ['nullable', 'string', 'max:255'],
            'additional_attendees.*.position_role' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $ticketCount = (int) $this->input('ticket_count', 1);
            $expectedAdditional = max(0, $ticketCount - 1);

            $rawAttendees = $this->input('additional_attendees', []);
            $attendees = is_array($rawAttendees) ? $rawAttendees : [];

            $filledAttendees = array_values(array_filter($attendees, function ($attendee) {
                if (!is_array($attendee)) {
                    return false;
                }

                return collect([
                    'full_name',
                    'school_name',
                    'phone_number',
                    'province_region',
                    'district',
                    'position_role',
                ])->contains(fn (string $field) => filled($attendee[$field] ?? null));
            }));

            if (count($filledAttendees) !== $expectedAdditional) {
                $validator->errors()->add(
                    'additional_attendees',
                    "Please provide details for {$expectedAdditional} additional attendee(s)."
                );
                return;
            }

            foreach ($filledAttendees as $index => $attendee) {
                $attendeeNumber = $index + 2;
                $requiredFields = [
                    'full_name' => 'Full name',
                    'school_name' => 'School name',
                    'phone_number' => 'Phone number',
                    'province_region' => 'Province/Region',
                    'district' => 'District',
                    'position_role' => 'Position/Role',
                ];

                foreach ($requiredFields as $field => $label) {
                    if (blank($attendee[$field] ?? null)) {
                        $validator->errors()->add(
                            "additional_attendees.{$index}.{$field}",
                            "Attendee {$attendeeNumber} {$label} is required."
                        );
                    }
                }
            }
        });
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
            'additional_attendees.*.phone_number.regex' => 'Please enter a valid phone number format for each additional attendee.',
        ];
    }
}
