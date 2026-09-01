<?php

namespace App\Domain\Billing;

use App\Domain\Billing\Contracts\BillingGateway;
use App\Domain\Billing\Models\PlanPrice;
use App\Domain\Billing\Services\MercadoPagoBillingGateway;
use App\Domain\Billing\Services\PaddleBillingGateway;
use App\Domain\Billing\Services\PayPalBillingGateway;
use App\Domain\Billing\Services\PaystackBillingGateway;
use App\Domain\Billing\Services\RazorpayBillingGateway;
use App\Domain\Billing\Services\StripeBillingGateway;
use App\Domain\Settings\Settings\PaymentGatewaySettings;
use App\Models\Tenant;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class BillingManager extends Manager implements BillingGateway
{
    protected ?PaymentGatewaySettings $settings = null;

    public function getSettings(): PaymentGatewaySettings
    {
        if (! $this->settings) {
            $this->settings = app(PaymentGatewaySettings::class);
        }

        return $this->settings;
    }

    public function getDefaultDriver(): string
    {
        try {
            return $this->getSettings()->default_gateway ?? config('domains.billing.default_gateway', 'stripe');
        } catch (\Throwable) {
            return config('domains.billing.default_gateway', 'stripe');
        }
    }

    /**
     * Resolve the gateway appropriate for a specific PlanPrice.
     */
    public function forPlanPrice(PlanPrice $planPrice): BillingGateway
    {
        if (! empty($planPrice->gateway)) {
            return $this->driver($planPrice->gateway);
        }

        if ($this->getSettings()->auto_select_by_currency) {
            return $this->forCurrency($planPrice->currency);
        }

        return $this->driver();
    }

    /**
     * Resolve the gateway based on currency.
     */
    public function forCurrency(string $currency): BillingGateway
    {
        $currency = strtoupper(trim($currency));

        return match ($currency) {
            'INR' => $this->getSettings()->razorpay_enabled ? $this->driver('razorpay') : $this->driver(),
            'BRL' => $this->getSettings()->mercadopago_enabled ? $this->driver('mercadopago') : $this->driver(),
            'NGN', 'GHS', 'KES', 'ZAR' => $this->getSettings()->paystack_enabled ? $this->driver('paystack') : $this->driver(),
            default => $this->driver(),
        };
    }

    /**
     * Resolve the gateway for a tenant.
     */
    public function forTenant(Tenant $tenant): BillingGateway
    {
        if (! empty($tenant->preferred_gateway)) {
            return $this->driver($tenant->preferred_gateway);
        }

        if (! empty($tenant->default_currency) && $this->getSettings()->auto_select_by_currency) {
            return $this->forCurrency($tenant->default_currency);
        }

        return $this->driver();
    }

    public function createStripeDriver(): BillingGateway
    {
        return new StripeBillingGateway($this->getSettings());
    }

    public function createPaddleDriver(): BillingGateway
    {
        return new PaddleBillingGateway($this->getSettings());
    }

    public function createPaystackDriver(): BillingGateway
    {
        return new PaystackBillingGateway($this->getSettings());
    }

    public function createRazorpayDriver(): BillingGateway
    {
        return new RazorpayBillingGateway($this->getSettings());
    }

    public function createMercadopagoDriver(): BillingGateway
    {
        return new MercadoPagoBillingGateway($this->getSettings());
    }

    public function createPaypalDriver(): BillingGateway
    {
        return new PayPalBillingGateway($this->getSettings());
    }

    // BillingGateway Contract Delegation to resolved driver
    public function createCheckoutSession(Tenant $tenant, PlanPrice $planPrice, string $returnUrl): string
    {
        return $this->forPlanPrice($planPrice)->createCheckoutSession($tenant, $planPrice, $returnUrl);
    }

    public function createCustomerPortalSession(Tenant $tenant, string $returnUrl): string
    {
        return $this->forTenant($tenant)->createCustomerPortalSession($tenant, $returnUrl);
    }

    public function cancelSubscription(Tenant $tenant): bool
    {
        return $this->forTenant($tenant)->cancelSubscription($tenant);
    }

    public function resumeSubscription(Tenant $tenant): bool
    {
        return $this->forTenant($tenant)->resumeSubscription($tenant);
    }
}
