<?php

namespace App\Domain\Settings\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentGatewaySettings extends Settings
{
    public string $default_gateway;

    public bool $auto_select_by_currency;

    // Stripe
    public bool $stripe_enabled;

    public ?string $stripe_key;

    public ?string $stripe_secret;

    public ?string $stripe_webhook_secret;

    // Paddle
    public bool $paddle_enabled;

    public bool $paddle_sandbox;

    public ?string $paddle_vendor_id;

    public ?string $paddle_api_key;

    public ?string $paddle_client_token;

    public ?string $paddle_webhook_secret;

    // Paystack
    public bool $paystack_enabled;

    public ?string $paystack_public_key;

    public ?string $paystack_secret_key;

    public ?string $paystack_webhook_secret;

    // Razorpay
    public bool $razorpay_enabled;

    public ?string $razorpay_key_id;

    public ?string $razorpay_key_secret;

    public ?string $razorpay_webhook_secret;

    // Mercado Pago
    public bool $mercadopago_enabled;

    public ?string $mercadopago_public_key;

    public ?string $mercadopago_access_token;

    public ?string $mercadopago_webhook_secret;

    // PayPal
    public bool $paypal_enabled;

    public bool $paypal_sandbox;

    public ?string $paypal_client_id;

    public ?string $paypal_client_secret;

    public ?string $paypal_webhook_id;

    public static function group(): string
    {
        return 'payment_gateways';
    }
}
