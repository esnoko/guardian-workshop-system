<form class="payment-options-form" method="post" action="{{ \Illuminate\Support\Facades\URL::signedRoute('payment.initiate', ['registration' => $registration->id]) }}">
    @csrf
    <div class="payment-options">
        <label class="payment-card" for="payment-method-payfast">
            <input id="payment-method-payfast" class="payment-method-input" type="radio" name="payment_method" value="payfast" checked>
            <span class="payment-method-control" aria-hidden="true"></span>
            <div class="payment-card-content">
                <div class="payment-card-head">
                    <div class="payment-card-brand">
                        <img class="payment-brand-logo payment-brand-logo--payfast" src="{{ asset('images/payfast-logo.png') }}" alt="PayFast">
                        <p>Pay securely via PayFast</p>
                    </div>
                </div>
                <ul class="payment-list">
                    <li><img class="payment-list-icon payment-list-icon-img" src="{{ asset('images/Instant_eft.png') }}" alt="" aria-hidden="true">Instant eft</li>
                    <li><img class="payment-list-icon payment-list-icon-img" src="{{ asset('images/Credit_Debit_Card.png') }}" alt="" aria-hidden="true">Credit &amp; Debit Card</li>
                    <li><img class="payment-list-icon payment-list-icon-img" src="{{ asset('images/Secure.png') }}" alt="" aria-hidden="true">Secure and trusted payments</li>
                </ul>
            </div>
        </label>

        <label class="payment-card" for="payment-method-payflex">
            <input id="payment-method-payflex" class="payment-method-input" type="radio" name="payment_method" value="payflex">
            <span class="payment-method-control" aria-hidden="true"></span>
            <div class="payment-card-content">
                <div class="payment-card-head">
                    <div class="payment-card-brand">
                        <img class="payment-brand-logo payment-brand-logo--payflex" src="{{ asset('images/payflex-logo.png') }}" alt="PayFlex">
                        <p>Get it now, pay later with payflex</p>
                    </div>
                </div>
                <ul class="payment-list">
                    <li><img class="payment-list-icon payment-list-icon-img" src="{{ asset('images/pay_in_3.png') }}" alt="" aria-hidden="true">Pay in 3 interest-free payments</li>
                    <li><img class="payment-list-icon payment-list-icon-img" src="{{ asset('images/no_fees.png') }}" alt="" aria-hidden="true">No fees when you pay on time</li>
                    <li><img class="payment-list-icon payment-list-icon-img" src="{{ asset('images/Secure.png') }}" alt="" aria-hidden="true">Quick and easy application</li>
                </ul>
                <p class="payment-note">Certificate will be withheld until payment is complete</p>
            </div>
        </label>
    </div>
    <button class="payment-action payment-action--primary" type="submit">Continue with selected method</button>
</form>
