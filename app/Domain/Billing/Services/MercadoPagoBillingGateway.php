<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Contracts\BillingGateway;
use App\Domain\Billing\Models\PlanPrice;
use App\Domain\Settings\Settings\PaymentGatewaySettings;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoBillingGateway implements BillingGateway
{
    protected PaymentGatewaySettings $settings;

    protected string $baseUrl = 'https://api.mercadopago.com';

    public function __construct(?PaymentGatewaySettings $settings = null)
    {
        $this->settings = $settings ?? app(PaymentGatewaySettings::class);
    }

    public function createCheckoutSession(Tenant $tenant, PlanPrice $planPrice, string $returnUrl): string
    {
        $accessToken = $this->settings->mercadopago_access_token;

        if (empty($accessToken)) {
            return $returnUrl.'?error=mercadopago_not_configured';
        }

        try {
            $decimalAmount = $planPrice->amount / 100;

            // If a preapproval plan ID is configured, create a recurring subscription preapproval
            if (! empty($planPrice->gateway_price_id)) {
                $response = Http::withToken($accessToken)
                    ->post("{$this->baseUrl}/preapproval", [
                        'preapproval_plan_id' => $planPrice->gateway_price_id,
                        'payer_email' => $tenant->email,
                        'back_url' => $returnUrl.'?gateway=mercadopago&success=true',
                        'external_reference' => $tenant->id,
                        'reason' => ($planPrice->plan?->name ?? 'Subscription').' Plan',
                        'auto_recurring' => [
                            'frequency' => 1,
                            'frequency_type' => $planPrice->plan?->billing_period === 'yearly' ? 'years' : 'months',
                            'transaction_amount' => $decimalAmount,
                            'currency_id' => strtoupper($planPrice->currency),
                        ],
                    ]);

                if ($response->successful() && isset($response->json()['init_point'])) {
                    return $response->json()['init_point'];
                }
            }

            // Fallback: Mercado Pago Standard Preference Checkout
            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/checkout/preferences", [
                    'items' => [
                        [
                            'title' => ($planPrice->plan?->name ?? 'Subscription').' Plan',
                            'quantity' => 1,
                            'unit_price' => (float) $decimalAmount,
                            'currency_id' => strtoupper($planPrice->currency),
                        ],
                    ],
                    'payer' => [
                        'email' => $tenant->email,
                        'name' => $tenant->name,
                    ],
                    'back_urls' => [
                        'success' => $returnUrl.'?gateway=mercadopago&success=true',
                        'failure' => $returnUrl.'?gateway=mercadopago&canceled=true',
                        'pending' => $returnUrl.'?gateway=mercadopago&pending=true',
                    ],
                    'auto_return' => 'approved',
                    'external_reference' => $tenant->id,
                    'metadata' => [
                        'tenant_id' => $tenant->id,
                        'plan_price_id' => $planPrice->id,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['init_point'] ?? ($data['sandbox_init_point'] ?? $returnUrl);
            }

            Log::warning('MercadoPago preference creation error', ['body' => $response->json()]);
        } catch (\Throwable $e) {
            Log::error('MercadoPago checkout creation failed: '.$e->getMessage());
        }

        return $returnUrl.'?error=mercadopago_checkout_failed';
    }

    public function createCustomerPortalSession(Tenant $tenant, string $returnUrl): string
    {
        return $returnUrl;
    }

    public function cancelSubscription(Tenant $tenant): bool
    {
        $preapprovalId = $tenant->data['mercadopago_preapproval_id'] ?? null;
        $accessToken = $this->settings->mercadopago_access_token;

        if (! $preapprovalId || ! $accessToken) {
            return false;
        }

        try {
            $response = Http::withToken($accessToken)
                ->put("{$this->baseUrl}/preapproval/{$preapprovalId}", [
                    'status' => 'cancelled',
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('MercadoPago subscription cancel failed: '.$e->getMessage());
            return false;
        }
    }

    public function resumeSubscription(Tenant $tenant): bool
    {
        $preapprovalId = $tenant->data['mercadopago_preapproval_id'] ?? null;
        $accessToken = $this->settings->mercadopago_access_token;

        if (! $preapprovalId || ! $accessToken) {
            return false;
        }

        try {
            $response = Http::withToken($accessToken)
                ->put("{$this->baseUrl}/preapproval/{$preapprovalId}", [
                    'status' => 'authorized',
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('MercadoPago subscription resume failed: '.$e->getMessage());
            return false;
        }
    }
}
