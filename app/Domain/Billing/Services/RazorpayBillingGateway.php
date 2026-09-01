<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Contracts\BillingGateway;
use App\Domain\Billing\Models\PlanPrice;
use App\Domain\Settings\Settings\PaymentGatewaySettings;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayBillingGateway implements BillingGateway
{
    protected PaymentGatewaySettings $settings;

    protected string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct(?PaymentGatewaySettings $settings = null)
    {
        $this->settings = $settings ?? app(PaymentGatewaySettings::class);
    }

    public function createCheckoutSession(Tenant $tenant, PlanPrice $planPrice, string $returnUrl): string
    {
        $keyId = $this->settings->razorpay_key_id;
        $keySecret = $this->settings->razorpay_key_secret;

        if (empty($keyId) || empty($keySecret)) {
            return $returnUrl.'?error=razorpay_not_configured';
        }

        try {
            $planId = $planPrice->gateway_price_id;

            if (! empty($planId)) {
                // Create subscription
                $response = Http::withBasicAuth($keyId, $keySecret)
                    ->post("{$this->baseUrl}/subscriptions", [
                        'plan_id' => $planId,
                        'total_count' => 120, // 10 years recurring
                        'quantity' => 1,
                        'customer_notify' => 1,
                        'notes' => [
                            'tenant_id' => $tenant->id,
                            'plan_price_id' => (string) $planPrice->id,
                        ],
                    ]);

                if ($response->successful() && isset($response->json()['short_url'])) {
                    return $response->json()['short_url'];
                }
            }

            // Fallback: Create Razorpay Standard Payment Link
            $response = Http::withBasicAuth($keyId, $keySecret)
                ->post("{$this->baseUrl}/payment_links", [
                    'amount' => $planPrice->amount,
                    'currency' => strtoupper($planPrice->currency),
                    'description' => ($planPrice->plan?->name ?? 'Subscription').' Plan',
                    'customer' => [
                        'name' => $tenant->name,
                        'email' => $tenant->email,
                    ],
                    'notes' => [
                        'tenant_id' => $tenant->id,
                        'plan_price_id' => (string) $planPrice->id,
                    ],
                    'callback_url' => $returnUrl.'?gateway=razorpay&success=true',
                    'callback_method' => 'get',
                ]);

            if ($response->successful() && isset($response->json()['short_url'])) {
                return $response->json()['short_url'];
            }

            Log::warning('Razorpay checkout initialization error', ['body' => $response->json()]);
        } catch (\Throwable $e) {
            Log::error('Razorpay checkout creation failed: '.$e->getMessage());
        }

        return $returnUrl.'?error=razorpay_checkout_failed';
    }

    public function createCustomerPortalSession(Tenant $tenant, string $returnUrl): string
    {
        return $returnUrl;
    }

    public function cancelSubscription(Tenant $tenant): bool
    {
        $subscriptionId = $tenant->data['razorpay_subscription_id'] ?? null;
        $keyId = $this->settings->razorpay_key_id;
        $keySecret = $this->settings->razorpay_key_secret;

        if (! $subscriptionId || ! $keyId || ! $keySecret) {
            return false;
        }

        try {
            $response = Http::withBasicAuth($keyId, $keySecret)
                ->post("{$this->baseUrl}/subscriptions/{$subscriptionId}/cancel", [
                    'cancel_at_cycle_end' => 1,
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Razorpay subscription cancel failed: '.$e->getMessage());
            return false;
        }
    }

    public function resumeSubscription(Tenant $tenant): bool
    {
        $subscriptionId = $tenant->data['razorpay_subscription_id'] ?? null;
        $keyId = $this->settings->razorpay_key_id;
        $keySecret = $this->settings->razorpay_key_secret;

        if (! $subscriptionId || ! $keyId || ! $keySecret) {
            return false;
        }

        try {
            $response = Http::withBasicAuth($keyId, $keySecret)
                ->post("{$this->baseUrl}/subscriptions/{$subscriptionId}/resume");

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Razorpay subscription resume failed: '.$e->getMessage());
            return false;
        }
    }
}
