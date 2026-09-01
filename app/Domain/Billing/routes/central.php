<?php

use App\Domain\Billing\Controllers\GatewayWebhookController;
use App\Domain\Billing\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Central unauthenticated payment webhooks
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handleWebhook'])->name('cashier.webhook');
Route::post('/webhooks/paddle', [GatewayWebhookController::class, 'handlePaddle'])->name('webhooks.paddle');
Route::post('/webhooks/paystack', [GatewayWebhookController::class, 'handlePaystack'])->name('webhooks.paystack');
Route::post('/webhooks/razorpay', [GatewayWebhookController::class, 'handleRazorpay'])->name('webhooks.razorpay');
Route::post('/webhooks/mercadopago', [GatewayWebhookController::class, 'handleMercadoPago'])->name('webhooks.mercadopago');
Route::post('/webhooks/paypal', [GatewayWebhookController::class, 'handlePayPal'])->name('webhooks.paypal');

