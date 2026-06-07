<form class="payment-options-form" method="post" action="{{ \Illuminate\Support\Facades\URL::signedRoute('payment.initiate', ['registration' => $registration->id]) }}">
    @csrf
    <div class="payment-options">
        <label class="payment-card" for="payment-method-payfast">
            <input id="payment-method-payfast" class="payment-method-input" type="radio" name="payment_method" value="payfast" checked>
            <span class="payment-method-control" aria-hidden="true"></span>
            <div class="payment-card-content">
                <div class="payment-card-head">
                    <div class="payment-card-brand">
                        @if (file_exists(public_path('images/payment/payfast-logo.png')))
                            <img class="payment-brand-logo payment-brand-logo--payfast" src="{{ asset('images/payment/payfast-logo.png') }}" alt="PayFast">
                        @else
                            <h2>PayFast</h2>
                        @endif
                        <p>Pay securely via PayFast</p>
                    </div>
                </div>
                <ul class="payment-list">
                    <li><span class="payment-list-icon" aria-hidden="true"></span>Instant eft</li>
                    <li><span class="payment-list-icon" aria-hidden="true"></span>Credit &amp; Debit Card</li>
                    <li><span class="payment-list-icon" aria-hidden="true"></span>Secure and trusted payments</li>
                </ul>
            </div>
        </label>

        <label class="payment-card" for="payment-method-payflex">
            <input id="payment-method-payflex" class="payment-method-input" type="radio" name="payment_method" value="payflex">
            <span class="payment-method-control" aria-hidden="true"></span>
            <div class="payment-card-content">
                <div class="payment-card-head">
                    <div class="payment-card-brand">
                        <h2>payflex</h2>
                        <p>Get it now, pay later with payflex</p>
                    </div>
                </div>
                <ul class="payment-list">
                    <li><span class="payment-list-icon" aria-hidden="true"></span>Pay in 3 interest-free payments</li>
                    <li><span class="payment-list-icon" aria-hidden="true"></span>No fees when you pay on time</li>
                    <li><span class="payment-list-icon" aria-hidden="true"></span>Quick and easy application</li>
                </ul>
                <p class="payment-note">Certificate will be withheld until payment is complete</p>
            </div>
        </label>
    </div>
    <button class="payment-action payment-action--primary" type="submit">Continue with selected method</button>
</form>
