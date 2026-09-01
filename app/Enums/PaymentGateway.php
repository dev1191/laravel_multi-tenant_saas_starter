<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentGateway: string implements HasColor, HasIcon, HasLabel
{
    case Stripe = 'stripe';
    case Paddle = 'paddle';
    case Paystack = 'paystack';
    case Razorpay = 'razorpay';
    case MercadoPago = 'mercadopago';
    case PayPal = 'paypal';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Stripe => __('messages.payment_gateways.stripe') ?? 'Stripe',
            self::Paddle => __('messages.payment_gateways.paddle') ?? 'Paddle',
            self::Paystack => __('messages.payment_gateways.paystack') ?? 'Paystack',
            self::Razorpay => __('messages.payment_gateways.razorpay') ?? 'Razorpay',
            self::MercadoPago => __('messages.payment_gateways.mercadopago') ?? 'Mercado Pago',
            self::PayPal => __('messages.payment_gateways.paypal') ?? 'PayPal',
        };
    }

    public function getFullLabel(): string
    {
        return match ($this) {
            self::Stripe => __('messages.payment_gateways.stripe_full') ?? 'Stripe (Global - USD, EUR, GBP, CAD, AUD)',
            self::Paddle => __('messages.payment_gateways.paddle_full') ?? 'Paddle (Global Merchant of Record - 200+ Countries)',
            self::Paystack => __('messages.payment_gateways.paystack_full') ?? 'Paystack (Africa - NGN, GHS, KES, ZAR)',
            self::Razorpay => __('messages.payment_gateways.razorpay_full') ?? 'Razorpay (India & South Asia - INR)',
            self::MercadoPago => __('messages.payment_gateways.mercadopago_full') ?? 'Mercado Pago (Latin America - BRL, MXN, ARS, CLP, COP)',
            self::PayPal => __('messages.payment_gateways.paypal_full') ?? 'PayPal (Worldwide)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Stripe => 'info',
            self::Paddle => 'primary',
            self::Paystack => 'success',
            self::Razorpay => 'warning',
            self::MercadoPago => 'info',
            self::PayPal => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Stripe => 'heroicon-o-banknotes',
            self::Paddle => 'heroicon-o-globe-alt',
            self::Paystack => 'heroicon-o-currency-dollar',
            self::Razorpay => 'heroicon-o-bolt',
            self::MercadoPago => 'heroicon-o-shopping-bag',
            self::PayPal => 'heroicon-o-arrow-path-rounded-square',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $gateway) => [
            $gateway->value => $gateway->getLabel() ?? $gateway->value,
        ])->toArray();
    }

    /**
     * @return array<string, string>
     */
    public static function fullOptions(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $gateway) => [
            $gateway->value => $gateway->getFullLabel(),
        ])->toArray();
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
