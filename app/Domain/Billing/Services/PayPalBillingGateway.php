<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Contracts\BillingGateway;
use App\Domain\Billing\Models\PlanPrice;
use App\Domain\Settings\Settings\PaymentGatewaySettings;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalBillingGateway implements BillingGateway
{
    protected PaymentGatewaySettings $settings;

    protected string $baseUrl;

    public function __construct(?PaymentGatewaySettings $settings = null)
    {
        $this->settings = $settings ?? app(PaymentGatewaySettings::class);
        $this->baseUrl = $this->settings->paypal_sandbox
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    protected function getAccessToken(): ?string
    {
        $clientId = $this->settings->paypal_client_id;
        $clientSecret = $this->settings->paypal_client_secret;

        if (empty($clientId) || empty($clientSecret)) {
            return null;
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->post("{$this->baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                return $response->json()['access_token'] ?? null;
            }
        } catch (\Throwable $e) {
            Log::error('PayPal auth token request failed: '.$e->getMessage());
        }

        return null;
    }

    public function createCheckoutSession(Tenant $tenant, PlanPrice $planPrice, string $returnUrl): string
    {
        $token = $this->getAccessToken();

        if (empty($token)) {
            return $returnUrl.'?error=paypal_not_configured';
        }

        try {
            $planId = $planPrice->gateway_price_id;

            if (! empty($planId)) {
                // Create PayPal subscription
                $response = Http::withToken($token)
                    ->post("{$this->baseUrl}/v1/billing/subscriptions", [
                        'plan_id' => $planId,
                        'custom_id' => $tenant->id,
                        'subscriber' => [
                            'name' => [
                                'given_name' => $tenant->name,
                            ],
                            'email_address' => $tenant->email,
                        ],
                        'application_context' => [
                            'brand_name' => config('app.name'),
                            'locale' => 'en-US',
                            'shipping_preference' => 'NO_SHIPPING',
                            'user_action' => 'SUBSCRIBE_NOW',
                            'return_url' => $returnUrl.'?gateway=paypal&success=true',
                            'cancel_url' => $returnUrl.'?gateway=paypal&canceled=true',
                        ],
                    ]);

                if ($response->successful()) {
                    $links = $response->json()['links'] ?? [];
                    foreach ($links as $link) {
                        if (($link['rel'] ?? '') === 'approve') {
                            return $link['href'];
                        }
                    }
                }

                Log::warning('PayPal subscription creation warning', ['body' => $response->json()]);
            }

            // Fallback: One-time payment Order
            $decimalAmount = number_format($planPrice->amount / 100, 2, '.', '');
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/v2/checkout/orders", [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'reference_id' => $tenant->id,
                            'description' => ($planPrice->plan?->name ?? 'Subscription').' Plan',
                            'amount' => [
                                'currency_code' => strtoupper($planPrice->currency),
                                'value' => $decimalAmount,
                            ],
                        ],
                    ],
                    'application_context' => [
                        'return_url' => $returnUrl.'?gateway=paypal&success=true',
                        'cancel_url' => $returnUrl.'?gateway=paypal&canceled=true',
                    ],
                ]);

            if ($response->successful()) {
                $links = $response->json()['links'] ?? [];
                foreach ($links as $link) {
                    if (($link['rel'] ?? '') === 'approve') {
                        return $link['href'];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('PayPal checkout creation failed: '.$e->getMessage());
        }

        return $returnUrl.'?error=paypal_checkout_failed';
    }

    public function createCustomerPortalSession(Tenant $tenant, string $returnUrl): string
    {
        return $this->settings->paypal_sandbox
            ? 'https://www.sandbox.paypal.com/myaccount/autopay/'
            : 'https://www.paypal.com/myaccount/autopay/';
    }

    public function cancelSubscription(Tenant $tenant): bool
    {
        $subscriptionId = $tenant->data['paypal_subscription_id'] ?? null;
        $token = $this->getAccessToken();

        if (! $subscriptionId || ! $token) {
            return false;
        }

        try {
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}/cancel", [
                    'reason' => 'Tenant requested cancellation',
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('PayPal subscription cancel failed: '.$e->getMessage());
            return false;
        }
    }

    public function resumeSubscription(Tenant $tenant): bool
    {
        $subscriptionId = $tenant->data['paypal_subscription_id'] ?? null;
        $token = $this->getAccessToken();

        if (! $subscriptionId || ! $token) {
            return false;
        }

        try {
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}/activate", [
                    'reason' => 'Tenant requested reactivation',
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('PayPal subscription resume failed: '.$e->getMessage());
            return false;
        }
    }
}
