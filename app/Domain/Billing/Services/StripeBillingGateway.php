<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Contracts\BillingGateway;
use App\Domain\Billing\Models\PlanPrice;
use App\Domain\Settings\Settings\PaymentGatewaySettings;
use App\Models\Tenant;

class StripeBillingGateway implements BillingGateway
{
    public function __construct(
        protected ?PaymentGatewaySettings $settings = null
    ) {
        $this->settings = $settings ?? app(PaymentGatewaySettings::class);
        if (! empty($this->settings->stripe_secret)) {
            config(['cashier.secret' => $this->settings->stripe_secret]);
        }
        if (! empty($this->settings->stripe_key)) {
            config(['cashier.key' => $this->settings->stripe_key]);
        }
    }
    public function createCheckoutSession(Tenant $tenant, PlanPrice $planPrice, string $returnUrl): string
    {
        $priceId = $planPrice->gateway_price_id;

        if (empty($priceId)) {
            $checkout = $tenant->checkout([
                'price_data' => [
                    'currency' => strtolower($planPrice->currency),
                    'unit_amount' => $planPrice->amount,
                    'product_data' => [
                        'name' => $planPrice->plan->name.' Plan',
                    ],
                    'recurring' => [
                        'interval' => $planPrice->plan->billing_period === 'yearly' ? 'year' : 'month',
                    ],
                ],
            ], [
                'success_url' => $returnUrl.'?session_id={CHECKOUT_SESSION_ID}&success=true',
                'cancel_url' => $returnUrl.'?canceled=true',
            ]);

            return $checkout->url;
        }

        $checkout = $tenant->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => $returnUrl.'?session_id={CHECKOUT_SESSION_ID}&success=true',
                'cancel_url' => $returnUrl.'?canceled=true',
            ]);

        return $checkout->url;
    }

    public function createCustomerPortalSession(Tenant $tenant, string $returnUrl): string
    {
        return $tenant->billingPortalUrl($returnUrl);
    }

    public function cancelSubscription(Tenant $tenant): bool
    {
        $subscription = $tenant->subscription('default');

        if ($subscription && ! $subscription->canceled()) {
            $subscription->cancel();

            return true;
        }

        return false;
    }

    public function resumeSubscription(Tenant $tenant): bool
    {
        $subscription = $tenant->subscription('default');

        if ($subscription && $subscription->onGracePeriod()) {
            $subscription->resume();

            return true;
        }

        return false;
    }
}
