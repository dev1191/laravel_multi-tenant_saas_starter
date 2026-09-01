<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Contracts\BillingGateway;
use App\Domain\Billing\Models\PlanPrice;
use App\Domain\Settings\Settings\PaymentGatewaySettings;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaddleBillingGateway implements BillingGateway
{
    protected PaymentGatewaySettings $settings;

    protected string $baseUrl;

    public function __construct(?PaymentGatewaySettings $settings = null)
    {
        $this->settings = $settings ?? app(PaymentGatewaySettings::class);
        $this->baseUrl = $this->settings->paddle_sandbox
            ? 'https://sandbox-api.paddle.com'
            : 'https://api.paddle.com';
    }

    public function createCheckoutSession(Tenant $tenant, PlanPrice $planPrice, string $returnUrl): string
    {
        $apiKey = $this->settings->paddle_api_key;
        $priceId = $planPrice->gateway_price_id;

        if (empty($apiKey) || empty($priceId)) {
            // Fallback to direct checkout link or return URL with error
            return $returnUrl.'?error=paddle_not_configured';
        }

        try {
            $response = Http::withToken($apiKey)
                ->post("{$this->baseUrl}/transactions", [
                    'items' => [
                        [
                            'price_id' => $priceId,
                            'quantity' => 1,
                        ],
                    ],
                    'customer_id' => $tenant->data['paddle_customer_id'] ?? null,
                    'custom_data' => [
                        'tenant_id' => $tenant->id,
                        'plan_price_id' => $planPrice->id,
                    ],
                    'checkout' => [
                        'url' => $returnUrl,
                    ],
                ]);

            if ($response->successful() && isset($response->json()['data']['checkout']['url'])) {
                return $response->json()['data']['checkout']['url'];
            }

            Log::warning('Paddle checkout creation response error', ['body' => $response->json()]);
        } catch (\Throwable $e) {
            Log::error('Paddle checkout creation failed: '.$e->getMessage());
        }

        return $returnUrl.'?error=paddle_checkout_failed';
    }

    public function createCustomerPortalSession(Tenant $tenant, string $returnUrl): string
    {
        $customerId = $tenant->data['paddle_customer_id'] ?? null;
        $apiKey = $this->settings->paddle_api_key;

        if ($customerId && $apiKey) {
            try {
                $response = Http::withToken($apiKey)
                    ->post("{$this->baseUrl}/customers/{$customerId}/portal-sessions");

                if ($response->successful() && isset($response->json()['data']['urls']['general']['overview'])) {
                    return $response->json()['data']['urls']['general']['overview'];
                }
            } catch (\Throwable $e) {
                Log::error('Paddle portal creation failed: '.$e->getMessage());
            }
        }

        return $returnUrl;
    }

    public function cancelSubscription(Tenant $tenant): bool
    {
        $subscriptionId = $tenant->data['paddle_subscription_id'] ?? null;
        $apiKey = $this->settings->paddle_api_key;

        if (! $subscriptionId || ! $apiKey) {
            return false;
        }

        try {
            $response = Http::withToken($apiKey)
                ->post("{$this->baseUrl}/subscriptions/{$subscriptionId}/cancel", [
                    'effective_from' => 'next_billing_period',
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Paddle subscription cancel failed: '.$e->getMessage());
            return false;
        }
    }

    public function resumeSubscription(Tenant $tenant): bool
    {
        $subscriptionId = $tenant->data['paddle_subscription_id'] ?? null;
        $apiKey = $this->settings->paddle_api_key;

        if (! $subscriptionId || ! $apiKey) {
            return false;
        }

        try {
            $response = Http::withToken($apiKey)
                ->post("{$this->baseUrl}/subscriptions/{$subscriptionId}/activate");

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Paddle subscription resume failed: '.$e->getMessage());
            return false;
        }
    }
}
