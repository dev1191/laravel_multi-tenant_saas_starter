<?php

namespace App\Domain\Billing\Controllers;

use App\Domain\Settings\Settings\PaymentGatewaySettings;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GatewayWebhookController extends Controller
{
    public function __construct(
        protected PaymentGatewaySettings $settings
    ) {}

    /**
     * Handle incoming Paddle webhook.
     */
    public function handlePaddle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $eventType = $payload['event_type'] ?? '';
        $data = $payload['data'] ?? [];

        Log::info('Paddle webhook received', ['event' => $eventType]);

        $tenantId = $data['custom_data']['tenant_id'] ?? null;
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if (! $tenant && isset($data['customer_id'])) {
            $tenant = Tenant::where('data->paddle_customer_id', $data['customer_id'])->first();
        }

        if ($tenant) {
            match ($eventType) {
                'subscription.activated', 'subscription.resumed', 'transaction.completed' => $tenant->update([
                    'status' => 'active',
                    'data' => array_merge($tenant->data ?? [], [
                        'paddle_subscription_id' => $data['id'] ?? ($tenant->data['paddle_subscription_id'] ?? null),
                        'paddle_customer_id' => $data['customer_id'] ?? ($tenant->data['paddle_customer_id'] ?? null),
                    ]),
                ]),
                'subscription.canceled', 'subscription.past_due' => $tenant->update(['status' => 'suspended']),
                default => null,
            };
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle incoming Paystack webhook.
     */
    public function handlePaystack(Request $request): JsonResponse
    {
        $secretKey = $this->settings->paystack_secret_key;
        $signature = $request->header('x-paystack-signature');

        if ($secretKey && $signature) {
            $computed = hash_hmac('sha512', $request->getContent(), $secretKey);
            if (! hash_equals($computed, $signature)) {
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        $event = $request->input('event');
        $data = $request->input('data', []);

        Log::info('Paystack webhook received', ['event' => $event]);

        $tenantId = $data['metadata']['tenant_id'] ?? null;
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if (! $tenant && isset($data['customer']['email'])) {
            $tenant = Tenant::where('email', $data['customer']['email'])->first();
        }

        if ($tenant) {
            match ($event) {
                'charge.success', 'subscription.create', 'subscription.enable' => $tenant->update([
                    'status' => 'active',
                    'data' => array_merge($tenant->data ?? [], [
                        'paystack_customer_code' => $data['customer']['customer_code'] ?? null,
                        'paystack_subscription_code' => $data['subscription_code'] ?? null,
                        'paystack_email_token' => $data['email_token'] ?? null,
                    ]),
                ]),
                'subscription.disable', 'invoice.payment_failed' => $tenant->update(['status' => 'suspended']),
                default => null,
            };
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle incoming Razorpay webhook.
     */
    public function handleRazorpay(Request $request): JsonResponse
    {
        $webhookSecret = $this->settings->razorpay_webhook_secret;
        $signature = $request->header('x-razorpay-signature');

        if ($webhookSecret && $signature) {
            $computed = hash_hmac('sha256', $request->getContent(), $webhookSecret);
            if (! hash_equals($computed, $signature)) {
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        $event = $request->input('event');
        $payload = $request->input('payload', []);

        Log::info('Razorpay webhook received', ['event' => $event]);

        $notes = $payload['payment']['entity']['notes'] ?? ($payload['subscription']['entity']['notes'] ?? []);
        $tenantId = $notes['tenant_id'] ?? null;
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if ($tenant) {
            match ($event) {
                'payment.captured', 'subscription.activated', 'subscription.charged' => $tenant->update([
                    'status' => 'active',
                    'data' => array_merge($tenant->data ?? [], [
                        'razorpay_subscription_id' => $payload['subscription']['entity']['id'] ?? ($tenant->data['razorpay_subscription_id'] ?? null),
                    ]),
                ]),
                'subscription.cancelled', 'subscription.halted' => $tenant->update(['status' => 'suspended']),
                default => null,
            };
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle incoming MercadoPago webhook.
     */
    public function handleMercadoPago(Request $request): JsonResponse
    {
        $type = $request->input('type') ?? $request->input('topic');
        $data = $request->input('data', []);

        Log::info('MercadoPago webhook received', ['type' => $type]);

        $externalReference = $request->input('external_reference') ?? ($data['external_reference'] ?? null);
        $tenant = $externalReference ? Tenant::find($externalReference) : null;

        if ($tenant) {
            if (in_array($type, ['payment', 'preapproval', 'payment.created', 'payment.updated'])) {
                $status = $data['status'] ?? $request->input('status');
                if (in_array($status, ['approved', 'authorized', 'active'])) {
                    $tenant->update(['status' => 'active']);
                } elseif (in_array($status, ['cancelled', 'rejected', 'paused'])) {
                    $tenant->update(['status' => 'suspended']);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle incoming PayPal webhook.
     */
    public function handlePayPal(Request $request): JsonResponse
    {
        $eventType = $request->input('event_type');
        $resource = $request->input('resource', []);

        Log::info('PayPal webhook received', ['event' => $eventType]);

        $tenantId = $resource['custom_id'] ?? null;
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if ($tenant) {
            match ($eventType) {
                'BILLING.SUBSCRIPTION.ACTIVATED', 'BILLING.SUBSCRIPTION.RE-ACTIVATED', 'PAYMENT.SALE.COMPLETED' => $tenant->update([
                    'status' => 'active',
                    'data' => array_merge($tenant->data ?? [], [
                        'paypal_subscription_id' => $resource['id'] ?? ($tenant->data['paypal_subscription_id'] ?? null),
                    ]),
                ]),
                'BILLING.SUBSCRIPTION.CANCELLED', 'BILLING.SUBSCRIPTION.EXPIRED', 'BILLING.SUBSCRIPTION.SUSPENDED' => $tenant->update(['status' => 'suspended']),
                default => null,
            };
        }

        return response()->json(['status' => 'success']);
    }
}
