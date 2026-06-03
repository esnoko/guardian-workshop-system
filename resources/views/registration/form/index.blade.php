<article class="registration-form-card">
    <header class="panel-head">
        <img src="{{ asset('images/Attendee_profile.png') }}" alt="Attendee profile" class="panel-icon-img">
        <div>
            <h2>Attendee Information</h2>
            <p>Please fill in your details below</p>
        </div>
    </header>

    <form class="registration-form" action="#" method="post">
        @csrf

        <label>
            Full Name<span class="required">*</span>
            <input type="text" name="full_name" placeholder="Enter full name and surname" required>
        </label>

        <label>
            School Name<span class="required">*</span>
            <input type="text" name="school_name" placeholder="Enter your school name" required>
        </label>

        <label>
            Email Address<span class="required">*</span>
            <input type="email" name="email_address" placeholder="Enter your email address" required>
        </label>

        <label>
            Phone Number<span class="required">*</span>
            <input type="text" name="phone_number" placeholder="Enter your phone number" required>
        </label>

        <div class="split-fields">
            <label>
                Province / Region<span class="required">*</span>
                <select name="province_region" required>
                    <option value="">Select Province/Region</option>
                    <option>Gauteng</option>
                    <option>Western Cape</option>
                    <option>KwaZulu-Natal</option>
                </select>
            </label>

            <label>
                District<span class="required">*</span>
                <select name="district" required>
                    <option value="">Select District</option>
                    <option>Johannesburg North</option>
                    <option>Johannesburg South</option>
                    <option>Ekurhuleni</option>
                </select>
            </label>
        </div>

        <label>
            Position / Role
            <input type="text" name="position_role" placeholder="Enter your position / Role">
        </label>
    </form>
</article>
