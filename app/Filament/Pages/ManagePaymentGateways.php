<?php

namespace App\Filament\Pages;

use App\Domain\Settings\Settings\PaymentGatewaySettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use UnitEnum;

class ManagePaymentGateways extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.payment_gateways.title');
    }

    public function getTitle(): string
    {
        return __('messages.payment_gateways.settings_title');
    }

    protected static ?int $navigationSort = 5;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(PaymentGatewaySettings::class);
        $this->data = $settings->toArray();
    }

    public function content(Schema $schema): Schema
    {
        $appUrl = config('app.url', 'http://localhost');

        return $schema->components([
            Form::make()
                ->statePath('data')
                ->schema([
                    Tabs::make('Gateways')
                        ->tabs([
                            Tab::make('General')
                                ->icon('heroicon-o-adjustments-horizontal')
                                ->schema([
                                    Section::make('Routing & Defaults')
                                        ->description('Configure primary gateway fallback and dynamic regional routing.')
                                        ->schema([
                                            Select::make('default_gateway')
                                                ->label(__('messages.payment_gateways.default_gateway'))
                                                ->options([
                                                    'stripe' => 'Stripe (Global / US / EU)',
                                                    'paddle' => 'Paddle (Merchant of Record / Global)',
                                                    'paystack' => 'Paystack (Africa - NGN, GHS, KES, ZAR)',
                                                    'razorpay' => 'Razorpay (India / South Asia - INR)',
                                                    'mercadopago' => 'Mercado Pago (Latin America - BRL, MXN, ARS)',
                                                    'paypal' => 'PayPal (Worldwide)',
                                                ])
                                                ->required(),

                                            Toggle::make('auto_select_by_currency')
                                                ->label(__('messages.payment_gateways.auto_route_currency'))
                                                ->helperText(__('messages.payment_gateways.auto_route_help'))
                                                ->default(true),
                                        ])->columns(2),
                                ]),

                            Tab::make('Stripe')
                                ->icon('heroicon-o-banknotes')
                                ->schema([
                                    Section::make('Stripe Configuration')
                                        ->description('Global credit card processing and subscriptions via Stripe.')
                                        ->schema([
                                            Toggle::make('stripe_enabled')
                                                ->label('Enable Stripe')
                                                ->columnSpanFull(),

                                            TextInput::make('stripe_key')
                                                ->label('Publishable Key')
                                                ->placeholder('pk_live_... / pk_test_...'),

                                            TextInput::make('stripe_secret')
                                                ->label('Secret Key')
                                                ->password()
                                                ->revealable()
                                                ->placeholder('sk_live_... / sk_test_...'),

                                            TextInput::make('stripe_webhook_secret')
                                                ->label('Webhook Signing Secret')
                                                ->password()
                                                ->revealable()
                                                ->placeholder('whsec_...'),

                                            TextInput::make('_stripe_webhook_url')
                                                ->label('Webhook Endpoint URL')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->default("{$appUrl}/webhooks/stripe")
                                                ->helperText('Configure this URL in your Stripe Dashboard under Developers > Webhooks.'),
                                        ])->columns(2),
                                ]),

                            Tab::make('Paddle')
                                ->icon('heroicon-o-globe-alt')
                                ->schema([
                                    Section::make('Paddle Configuration (Merchant of Record)')
                                        ->description('Handles global tax, VAT, compliance, and multi-currency billing in 200+ countries.')
                                        ->schema([
                                            Toggle::make('paddle_enabled')
                                                ->label('Enable Paddle'),

                                            Toggle::make('paddle_sandbox')
                                                ->label('Sandbox Mode')
                                                ->default(true),

                                            TextInput::make('paddle_vendor_id')
                                                ->label('Vendor / Account ID'),

                                            TextInput::make('paddle_api_key')
                                                ->label('API Key (Bearer Token)')
                                                ->password()
                                                ->revealable(),

                                            TextInput::make('paddle_client_token')
                                                ->label('Client-Side Token (for overlay/inline checkout)'),

                                            TextInput::make('paddle_webhook_secret')
                                                ->label('Webhook Secret Key')
                                                ->password()
                                                ->revealable(),

                                            TextInput::make('_paddle_webhook_url')
                                                ->label('Webhook Endpoint URL')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->default("{$appUrl}/webhooks/paddle")
                                                ->helperText('Set this destination URL in Paddle Billing > Notifications.'),
                                        ])->columns(2),
                                ]),

                            Tab::make('Paystack')
                                ->icon('heroicon-o-currency-dollar')
                                ->schema([
                                    Section::make('Paystack Configuration (Africa)')
                                        ->description('Popular payment rail for Nigeria, Ghana, Kenya, South Africa, and Côte d\'Ivoire.')
                                        ->schema([
                                            Toggle::make('paystack_enabled')
                                                ->label('Enable Paystack')
                                                ->columnSpanFull(),

                                            TextInput::make('paystack_public_key')
                                                ->label('Public Key')
                                                ->placeholder('pk_live_... / pk_test_...'),

                                            TextInput::make('paystack_secret_key')
                                                ->label('Secret Key')
                                                ->password()
                                                ->revealable()
                                                ->placeholder('sk_live_... / sk_test_...'),

                                            TextInput::make('paystack_webhook_secret')
                                                ->label('Webhook Secret / Key')
                                                ->password()
                                                ->revealable(),

                                            TextInput::make('_paystack_webhook_url')
                                                ->label('Webhook Endpoint URL')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->default("{$appUrl}/webhooks/paystack")
                                                ->helperText('Configure this callback in Paystack Dashboard > Settings > API Keys & Webhooks.'),
                                        ])->columns(2),
                                ]),

                            Tab::make('Razorpay')
                                ->icon('heroicon-o-bolt')
                                ->schema([
                                    Section::make('Razorpay Configuration (India & South Asia)')
                                        ->description('Supports UPI, RuPay, NetBanking, and credit/debit cards.')
                                        ->schema([
                                            Toggle::make('razorpay_enabled')
                                                ->label('Enable Razorpay')
                                                ->columnSpanFull(),

                                            TextInput::make('razorpay_key_id')
                                                ->label('Key ID')
                                                ->placeholder('rzp_live_... / rzp_test_...'),

                                            TextInput::make('razorpay_key_secret')
                                                ->label('Key Secret')
                                                ->password()
                                                ->revealable(),

                                            TextInput::make('razorpay_webhook_secret')
                                                ->label('Webhook Secret')
                                                ->password()
                                                ->revealable(),

                                            TextInput::make('_razorpay_webhook_url')
                                                ->label('Webhook Endpoint URL')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->default("{$appUrl}/webhooks/razorpay")
                                                ->helperText('Add this webhook in Razorpay Dashboard > Settings > Webhooks.'),
                                        ])->columns(2),
                                ]),

                            Tab::make('Mercado Pago')
                                ->icon('heroicon-o-shopping-bag')
                                ->schema([
                                    Section::make('Mercado Pago Configuration (Latin America)')
                                        ->description('Supports PIX, Boleto, and regional cards across Brazil, Mexico, Argentina, Colombia, and Chile.')
                                        ->schema([
                                            Toggle::make('mercadopago_enabled')
                                                ->label('Enable Mercado Pago')
                                                ->columnSpanFull(),

                                            TextInput::make('mercadopago_public_key')
                                                ->label('Public Key')
                                                ->placeholder('APP_USR-... / TEST-...'),

                                            TextInput::make('mercadopago_access_token')
                                                ->label('Access Token')
                                                ->password()
                                                ->revealable()
                                                ->placeholder('APP_USR-...'),

                                            TextInput::make('mercadopago_webhook_secret')
                                                ->label('Webhook Secret / Verification Token')
                                                ->password()
                                                ->revealable(),

                                            TextInput::make('_mercadopago_webhook_url')
                                                ->label('Webhook Endpoint URL')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->default("{$appUrl}/webhooks/mercadopago")
                                                ->helperText('Configure this IPN/Webhook URL in Mercado Pago Developer panel.'),
                                        ])->columns(2),
                                ]),

                            Tab::make('PayPal')
                                ->icon('heroicon-o-arrow-path-rounded-square')
                                ->schema([
                                    Section::make('PayPal Configuration (Worldwide)')
                                        ->description('Global PayPal wallets, Pay in 4, and card checkout.')
                                        ->schema([
                                            Toggle::make('paypal_enabled')
                                                ->label('Enable PayPal'),

                                            Toggle::make('paypal_sandbox')
                                                ->label('Sandbox Mode')
                                                ->default(true),

                                            TextInput::make('paypal_client_id')
                                                ->label('Client ID'),

                                            TextInput::make('paypal_client_secret')
                                                ->label('Client Secret')
                                                ->password()
                                                ->revealable(),

                                            TextInput::make('paypal_webhook_id')
                                                ->label('Webhook ID')
                                                ->placeholder('9AB...'),

                                            TextInput::make('_paypal_webhook_url')
                                                ->label('Webhook Endpoint URL')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->default("{$appUrl}/webhooks/paypal")
                                                ->helperText('Add this webhook in PayPal Developer Dashboard > My Apps & Credentials.'),
                                        ])->columns(2),
                                ]),
                        ]),
                ]),

            Actions::make([
                Action::make('save')
                    ->label(__('messages.payment_gateways.save_settings'))
                    ->action('save')
                    ->color('primary'),
            ]),
        ]);
    }

    public function save(): void
    {
        $settings = app(PaymentGatewaySettings::class);

        foreach ($this->data as $key => $value) {
            if (! str_starts_with($key, '_') && property_exists($settings, $key)) {
                $settings->{$key} = $value;
            }
        }

        $settings->save();

        Notification::make()
            ->title(__('messages.payment_gateways.saved_success'))
            ->success()
            ->send();
    }
}
