<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'default_gateway' => 'stripe',
            'auto_select_by_currency' => true,

            'stripe_enabled' => true,
            'stripe_key' => env('STRIPE_KEY'),
            'stripe_secret' => env('STRIPE_SECRET'),
            'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

            'paddle_enabled' => false,
            'paddle_sandbox' => true,
            'paddle_vendor_id' => env('PADDLE_VENDOR_ID'),
            'paddle_api_key' => env('PADDLE_API_KEY'),
            'paddle_client_token' => env('PADDLE_CLIENT_TOKEN'),
            'paddle_webhook_secret' => env('PADDLE_WEBHOOK_SECRET'),

            'paystack_enabled' => false,
            'paystack_public_key' => env('PAYSTACK_PUBLIC_KEY'),
            'paystack_secret_key' => env('PAYSTACK_SECRET_KEY'),
            'paystack_webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),

            'razorpay_enabled' => false,
            'razorpay_key_id' => env('RAZORPAY_KEY_ID'),
            'razorpay_key_secret' => env('RAZORPAY_KEY_SECRET'),
            'razorpay_webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),

            'mercadopago_enabled' => false,
            'mercadopago_public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
            'mercadopago_access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
            'mercadopago_webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),

            'paypal_enabled' => false,
            'paypal_sandbox' => true,
            'paypal_client_id' => env('PAYPAL_CLIENT_ID'),
            'paypal_client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'paypal_webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        ];

        foreach ($defaults as $name => $value) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'payment_gateways', 'name' => $name],
                [
                    'locked' => false,
                    'payload' => json_encode($value),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('group', 'payment_gateways')->delete();
    }
};
