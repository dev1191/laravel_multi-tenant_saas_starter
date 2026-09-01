<?php

use App\Domain\Billing\BillingManager;
use App\Domain\Billing\Contracts\BillingGateway;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\PlanPrice;
use App\Domain\Billing\Services\MercadoPagoBillingGateway;
use App\Domain\Billing\Services\PaddleBillingGateway;
use App\Domain\Billing\Services\PayPalBillingGateway;
use App\Domain\Billing\Services\PaystackBillingGateway;
use App\Domain\Billing\Services\RazorpayBillingGateway;
use App\Domain\Billing\Services\StripeBillingGateway;
use App\Domain\Settings\Settings\PaymentGatewaySettings;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('billing gateway resolves from service container as billing manager', function () {
    $gateway = app(BillingGateway::class);

    expect($gateway)->toBeInstanceOf(BillingManager::class);
});

test('billing manager creates all six gateway drivers', function () {
    /** @var BillingManager $manager */
    $manager = app(BillingManager::class);

    expect($manager->driver('stripe'))->toBeInstanceOf(StripeBillingGateway::class)
        ->and($manager->driver('paddle'))->toBeInstanceOf(PaddleBillingGateway::class)
        ->and($manager->driver('paystack'))->toBeInstanceOf(PaystackBillingGateway::class)
        ->and($manager->driver('razorpay'))->toBeInstanceOf(RazorpayBillingGateway::class)
        ->and($manager->driver('mercadopago'))->toBeInstanceOf(MercadoPagoBillingGateway::class)
        ->and($manager->driver('paypal'))->toBeInstanceOf(PayPalBillingGateway::class);
});

test('billing manager auto routes based on currency when enabled', function () {
    /** @var BillingManager $manager */
    $manager = app(BillingManager::class);
    $settings = app(PaymentGatewaySettings::class);

    $settings->auto_select_by_currency = true;
    $settings->razorpay_enabled = true;
    $settings->mercadopago_enabled = true;
    $settings->paystack_enabled = true;
    $settings->save();

    expect($manager->forCurrency('INR'))->toBeInstanceOf(RazorpayBillingGateway::class)
        ->and($manager->forCurrency('BRL'))->toBeInstanceOf(MercadoPagoBillingGateway::class)
        ->and($manager->forCurrency('NGN'))->toBeInstanceOf(PaystackBillingGateway::class)
        ->and($manager->forCurrency('USD'))->toBeInstanceOf(StripeBillingGateway::class);
});

test('multi gateway webhook endpoints receive events gracefully', function () {
    $paddleResponse = $this->postJson(route('webhooks.paddle'), [
        'event_type' => 'transaction.completed',
        'data' => [
            'id' => 'txn_123',
        ],
    ]);
    $paddleResponse->assertStatus(200);

    $paystackResponse = $this->postJson(route('webhooks.paystack'), [
        'event' => 'charge.success',
        'data' => [],
    ]);
    $paystackResponse->assertStatus(200);

    $razorpayResponse = $this->postJson(route('webhooks.razorpay'), [
        'event' => 'payment.captured',
        'payload' => [],
    ]);
    $razorpayResponse->assertStatus(200);

    $mercadopagoResponse = $this->postJson(route('webhooks.mercadopago'), [
        'type' => 'payment',
        'data' => ['status' => 'approved'],
    ]);
    $mercadopagoResponse->assertStatus(200);

    $paypalResponse = $this->postJson(route('webhooks.paypal'), [
        'event_type' => 'PAYMENT.SALE.COMPLETED',
        'resource' => [],
    ]);
    $paypalResponse->assertStatus(200);
});

test('filament manage payment gateways page renders successfully for central admin', function () {
    $user = \App\Domain\TenantAdmin\Models\CentralUser::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user, 'central')->get('/admin/manage-payment-gateways');
    $response->assertStatus(200);
});

