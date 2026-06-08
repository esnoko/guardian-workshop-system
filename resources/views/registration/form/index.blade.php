<article class="registration-form-card">
    <header class="panel-head">
        <img src="{{ asset('images/Attendee_profile.png') }}" alt="Attendee profile" class="panel-icon-img">
        <div>
            <h2>Attendee Information</h2>
            <p>Please fill in your details below</p>
        </div>
    </header>

    @if ($errors->any())
        <div class="form-errors">
            <p class="form-errors-title">Please correct the following errors:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="registration-form">
        <label>
             <span class="field-label">
            Full Name<span class="required">*</span>
             </span>
            <input type="text" name="full_name" placeholder="Enter full name and surname" value="{{ old('full_name') }}" required>
            @error('full_name')<span class="field-error">{{ $message }}</span>@enderror
        </label>

        <label>
            <span class="field-label">
            School Name<span class="required">*</span>
            </span>
            <input type="text" name="school_name" placeholder="Enter your school name" value="{{ old('school_name') }}" required>
            @error('school_name')<span class="field-error">{{ $message }}</span>@enderror
        </label>

        <label>
            <span class="field-label">
            Email Address<span class="required">*</span>
            </span>
            <input type="email" name="email_address" placeholder="Enter your email address" value="{{ old('email_address') }}" required>
            @error('email_address')<span class="field-error">{{ $message }}</span>@enderror
        </label>

        <label>
            <span class="field-label">
            Phone Number<span class="required">*</span>
            </span>
            <input type="text" name="phone_number" placeholder="Enter your phone number" value="{{ old('phone_number') }}" required>
            @error('phone_number')<span class="field-error">{{ $message }}</span>@enderror
        </label>

        <div class="split-fields">
            <label>
                <span class="field-label">
                Province / Region<span class="required">*</span>
                </span>
                <select name="province_region" required>
                    <option value="">Select Province/Region</option>
                    <option value="Gauteng" {{ old('province_region') === 'Gauteng' ? 'selected' : '' }}>Gauteng</option>
                    <option value="Western Cape" {{ old('province_region') === 'Western Cape' ? 'selected' : '' }}>Western Cape</option>
                    <option value="KwaZulu-Natal" {{ old('province_region') === 'KwaZulu-Natal' ? 'selected' : '' }}>KwaZulu-Natal</option>
                    <option value="Eastern Cape" {{ old('province_region') === 'Eastern Cape' ? 'selected' : '' }}>Eastern Cape</option>
                    <option value="Northern Cape" {{ old('province_region') === 'Northern Cape' ? 'selected' : '' }}>Northern Cape</option>
                    <option value="Free State" {{ old('province_region') === 'Free State' ? 'selected' : '' }}>Free State</option>
                    <option value="Limpopo" {{ old('province_region') === 'Limpopo' ? 'selected' : '' }}>Limpopo</option>
                    <option value="Mpumalanga" {{ old('province_region') === 'Mpumalanga' ? 'selected' : '' }}>Mpumalanga</option>
                </select>
                @error('province_region')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            <label>
                <span class="field-label">
                District<span class="required">*</span>
                </span>
                <select name="district" required>
                    <option value="">Select District</option>
                    <option value="Johannesburg North" {{ old('district') === 'Johannesburg North' ? 'selected' : '' }}>Johannesburg North</option>
                    <option value="Johannesburg South" {{ old('district') === 'Johannesburg South' ? 'selected' : '' }}>Johannesburg South</option>
                    <option value="Ekurhuleni" {{ old('district') === 'Ekurhuleni' ? 'selected' : '' }}>Ekurhuleni</option>
                    <option value="Tshwane" {{ old('district') === 'Tshwane' ? 'selected' : '' }}>Tshwane</option>
                </select>
                @error('district')<span class="field-error">{{ $message }}</span>@enderror
            </label>
        </div>

        <label>
            <span class="field-label">
            Position / Role<span class="required">*</span>
            </span>
            <input type="text" name="position_role" placeholder="Enter your position / Role" value="{{ old('position_role') }}" required>
            @error('position_role')<span class="field-error">{{ $message }}</span>@enderror
        </label>

        @php
            $currentSelectedTickets = (int) old('ticket_count', $selectedTickets);
        @endphp

        <section class="additional-attendees {{ $currentSelectedTickets <= 1 ? 'is-hidden' : '' }}" id="additionalAttendeesSection">
            <h3>Additional Attendee Details</h3>
            <p>Use the same booking email above. Add details for each extra attendee.</p>

            @for ($index = 0; $index < 2; $index++)
                <div class="additional-attendee-card" data-attendee-index="{{ $index }}">
                    <h4>Attendee {{ $index + 2 }}</h4>

                    <label>
                        <span class="field-label">
                        Full Name<span class="required">*</span>
                        </span>
                        <input type="text" name="additional_attendees[{{ $index }}][full_name]" value="{{ old("additional_attendees.{$index}.full_name") }}" placeholder="Enter full name and surname">
                        @error("additional_attendees.{$index}.full_name")<span class="field-error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="field-label">
                        School Name<span class="required">*</span>
                        </span>
                        <input type="text" name="additional_attendees[{{ $index }}][school_name]" value="{{ old("additional_attendees.{$index}.school_name") }}" placeholder="Enter school name">
                        @error("additional_attendees.{$index}.school_name")<span class="field-error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="field-label">
                        Phone Number<span class="required">*</span>
                        </span>
                        <input type="text" name="additional_attendees[{{ $index }}][phone_number]" value="{{ old("additional_attendees.{$index}.phone_number") }}" placeholder="Enter phone number">
                        @error("additional_attendees.{$index}.phone_number")<span class="field-error">{{ $message }}</span>@enderror
                    </label>

                    <div class="split-fields">
                        <label>
                            <span class="field-label">
                            Province / Region<span class="required">*</span>
                            </span>
                            <select name="additional_attendees[{{ $index }}][province_region]">
                                <option value="">Select Province/Region</option>
                                @foreach (['Gauteng', 'Western Cape', 'KwaZulu-Natal', 'Eastern Cape', 'Northern Cape', 'Free State', 'Limpopo', 'Mpumalanga'] as $province)
                                    <option value="{{ $province }}" {{ old("additional_attendees.{$index}.province_region") === $province ? 'selected' : '' }}>{{ $province }}</option>
                                @endforeach
                            </select>
                            @error("additional_attendees.{$index}.province_region")<span class="field-error">{{ $message }}</span>@enderror
                        </label>

                        <label>
                            <span class="field-label">
                            District<span class="required">*</span>
                            </span>
                            <select name="additional_attendees[{{ $index }}][district]">
                                <option value="">Select District</option>
                                @foreach (['Johannesburg North', 'Johannesburg South', 'Ekurhuleni', 'Tshwane'] as $district)
                                    <option value="{{ $district }}" {{ old("additional_attendees.{$index}.district") === $district ? 'selected' : '' }}>{{ $district }}</option>
                                @endforeach
                            </select>
                            @error("additional_attendees.{$index}.district")<span class="field-error">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    <label>
                        <span class="field-label">
                        Position / Role<span class="required">*</span>
                        </span>
                        <input type="text" name="additional_attendees[{{ $index }}][position_role]" value="{{ old("additional_attendees.{$index}.position_role") }}" placeholder="Enter position / role">
                        @error("additional_attendees.{$index}.position_role")<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                </div>
            @endfor
        </section>

        <input type="hidden" name="ticket_count" id="ticketCountInput" value="{{ old('ticket_count', $selectedTickets) }}">
    </div>
</article>
