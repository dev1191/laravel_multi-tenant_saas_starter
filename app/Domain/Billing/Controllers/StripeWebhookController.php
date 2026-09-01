<?php

namespace App\Domain\Billing\Controllers;

use App\Models\Tenant;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Handle customer subscription created or updated.
     */
    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionUpdated($payload);

        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;
        $status = $payload['data']['object']['status'] ?? null;

        if ($stripeCustomerId) {
            $tenant = Tenant::where('stripe_id', $stripeCustomerId)->first();
            if ($tenant) {
                if (in_array($status, ['active', 'trialing'])) {
                    $tenant->update(['status' => 'active']);
                } elseif (in_array($status, ['past_due', 'unpaid', 'canceled'])) {
                    $tenant->update(['status' => 'suspended']);
                }
            }
        }

        return $response;
    }

    /**
     * Handle customer subscription deleted.
     */
    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;

        if ($stripeCustomerId) {
            $tenant = Tenant::where('stripe_id', $stripeCustomerId)->first();
            if ($tenant) {
                $tenant->update(['status' => 'suspended']);
            }
        }

        return $response;
    }
}
