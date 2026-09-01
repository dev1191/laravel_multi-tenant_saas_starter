<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Contracts\BillingGateway;
use App\Domain\Billing\Models\PlanPrice;
use App\Domain\Settings\Settings\PaymentGatewaySettings;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackBillingGateway implements BillingGateway
{
    protected PaymentGatewaySettings $settings;

    protected string $baseUrl = 'https://api.paystack.co';

    public function __construct(?PaymentGatewaySettings $settings = null)
    {
        $this->settings = $settings ?? app(PaymentGatewaySettings::class);
    }

    public function createCheckoutSession(Tenant $tenant, PlanPrice $planPrice, string $returnUrl): string
    {
        $secretKey = $this->settings->paystack_secret_key;

        if (empty($secretKey)) {
            return $returnUrl.'?error=paystack_not_configured';
        }

        try {
            $payload = [
                'email' => $tenant->email,
                'amount' => $planPrice->amount, // in kobo / smallest unit
                'currency' => strtoupper($planPrice->currency),
                'callback_url' => $returnUrl.'?gateway=paystack&success=true',
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'plan_price_id' => $planPrice->id,
                    'plan_name' => $planPrice->plan?->name,
                ],
            ];

            if (! empty($planPrice->gateway_price_id)) {
                $payload['plan'] = $planPrice->gateway_price_id;
            }

            $response = Http::withToken($secretKey)
                ->post("{$this->baseUrl}/transaction/initialize", $payload);

            if ($response->successful() && isset($response->json()['data']['authorization_url'])) {
                return $response->json()['data']['authorization_url'];
            }

            Log::warning('Paystack checkout initialization error', ['body' => $response->json()]);
        } catch (\Throwable $e) {
            Log::error('Paystack checkout creation failed: '.$e->getMessage());
        }

        return $returnUrl.'?error=paystack_checkout_failed';
    }

    public function createCustomerPortalSession(Tenant $tenant, string $returnUrl): string
    {
        $customerCode = $tenant->data['paystack_customer_code'] ?? null;
        $secretKey = $this->settings->paystack_secret_key;

        if ($customerCode && $secretKey) {
            try {
                $response = Http::withToken($secretKey)
                    ->get("{$this->baseUrl}/customer/{$customerCode}");

                if ($response->successful()) {
                    return $returnUrl.'#paystack-portal';
                }
            } catch (\Throwable $e) {
                Log::error('Paystack portal check failed: '.$e->getMessage());
            }
        }

        return $returnUrl;
    }

    public function cancelSubscription(Tenant $tenant): bool
    {
        $subscriptionCode = $tenant->data['paystack_subscription_code'] ?? null;
        $emailToken = $tenant->data['paystack_email_token'] ?? null;
        $secretKey = $this->settings->paystack_secret_key;

        if (! $subscriptionCode || ! $secretKey) {
            return false;
        }

        try {
            $response = Http::withToken($secretKey)
                ->post("{$this->baseUrl}/subscription/disable", [
                    'code' => $subscriptionCode,
                    'token' => $emailToken ?? '',
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Paystack subscription disable failed: '.$e->getMessage());
            return false;
        }
    }

    public function resumeSubscription(Tenant $tenant): bool
    {
        $subscriptionCode = $tenant->data['paystack_subscription_code'] ?? null;
        $emailToken = $tenant->data['paystack_email_token'] ?? null;
        $secretKey = $this->settings->paystack_secret_key;

        if (! $subscriptionCode || ! $secretKey) {
            return false;
        }

        try {
            $response = Http::withToken($secretKey)
                ->post("{$this->baseUrl}/subscription/enable", [
                    'code' => $subscriptionCode,
                    'token' => $emailToken ?? '',
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Paystack subscription enable failed: '.$e->getMessage());
            return false;
        }
    }
}
