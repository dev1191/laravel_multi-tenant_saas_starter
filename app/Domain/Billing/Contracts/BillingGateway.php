<?php

namespace App\Domain\Billing\Contracts;

use App\Domain\Billing\Models\PlanPrice;
use App\Models\Tenant;

interface BillingGateway
{
    /**
     * Create a checkout session redirect URL for a specific plan price.
     */
    public function createCheckoutSession(Tenant $tenant, PlanPrice $planPrice, string $returnUrl): string;

    /**
     * Create a billing customer portal redirect URL.
     */
    public function createCustomerPortalSession(Tenant $tenant, string $returnUrl): string;

    /**
     * Cancel the tenant's current active subscription.
     */
    public function cancelSubscription(Tenant $tenant): bool;

    /**
     * Resume a canceled subscription on grace period.
     */
    public function resumeSubscription(Tenant $tenant): bool;
}
