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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('messages.payment_gateways.save_settings'))
                ->icon('heroicon-m-check-circle')
                ->action('save')
                ->color('primary')
                ->keyBindings(['mod+s']),
        ];
    }

    public function content(Schema $schema): Schema
    {
        $appUrl = rtrim(config('app.url', 'http://localhost'), '/');

        return $schema->components([
            Form::make()
                ->statePath('data')
                ->schema([
                    Tabs::make('Gateways')
                        ->contained(false)
                        ->scrollable(false)
                        ->tabs([
                            Tab::make('Overview & Routing')
                                ->icon('heroicon-o-adjustments-horizontal')
                                ->badge('Routing')
                                ->badgeColor('primary')
                                ->schema([
                                    Section::make('Multi-Gateway Orchestration')
                                        ->description('Configure dynamic regional gateway routing and fallback settings across your SaaS plans.')
                                        ->schema([
                                            Grid::make(2)->schema([
                                                Select::make('default_gateway')
                                                    ->label(__('messages.payment_gateways.default_gateway'))
                                                    ->options(\App\Enums\PaymentGateway::fullOptions())
                                                    ->helperText('Primary default gateway used when no currency-specific gateway is matched.')
                                                    ->required(),

                                                Toggle::make('auto_select_by_currency')
                                                    ->label(__('messages.payment_gateways.auto_route_currency'))
                                                    ->helperText(__('messages.payment_gateways.auto_route_help'))
                                                    ->inline(false)
                                                    ->onColor('success')
                                                    ->offColor('gray')
                                                    ->default(true),
                                            ]),
                                        ]),

                                    Section::make('Gateway Coverage Matrix')
                                        ->description('Active regional payment gateways configured in this platform.')
                                        ->schema([
                                            Grid::make(3)->schema([
                                                TextInput::make('_coverage_stripe')
                                                    ->label('Stripe')
                                                    ->default('US, EU, UK, Global Cards')
                                                    ->disabled()
                                                    ->dehydrated(false),

                                                TextInput::make('_coverage_paddle')
                                                    ->label('Paddle')
                                                    ->default('Global MoR (Tax & VAT handled)')
                                                    ->disabled()
                                                    ->dehydrated(false),

                                                TextInput::make('_coverage_paystack')
                                                    ->label('Paystack')
                                                    ->default('Nigeria, Ghana, Kenya, South Africa')
                                                    ->disabled()
                                                    ->dehydrated(false),

                                                TextInput::make('_coverage_razorpay')
                                                    ->label('Razorpay')
                                                    ->default('India (UPI, Cards, NetBanking)')
                                                    ->disabled()
                                                    ->dehydrated(false),

                                                TextInput::make('_coverage_mercadopago')
                                                    ->label('Mercado Pago')
                                                    ->default('Brazil (PIX), Mexico, Argentina')
                                                    ->disabled()
                                                    ->dehydrated(false),

                                                TextInput::make('_coverage_paypal')
                                                    ->label('PayPal')
                                                    ->default('Global PayPal Wallets & Cards')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                            ]),
                                        ])->collapsed(),
                                ]),

                            Tab::make('Stripe')
                                ->icon('heroicon-o-banknotes')
                                ->badge(fn () => ! empty($this->data['stripe_enabled']) ? 'Active' : 'Disabled')
                                ->badgeColor(fn () => ! empty($this->data['stripe_enabled']) ? 'success' : 'gray')
                                ->schema([
                                    Section::make('Stripe Gateway')
                                        ->description('Global credit card processing, SEPA, ACH, and subscription management via Stripe.')
                                        ->schema([
                                            Toggle::make('stripe_enabled')
                                                ->label('Enable Stripe Gateway')
                                                ->helperText('Activate Stripe checkout sessions and customer billing portal.')
                                                ->inline(false)
                                                ->onColor('success')
                                                ->offColor('gray'),

                                            Grid::make(2)->schema([
                                                TextInput::make('stripe_key')
                                                    ->label('Publishable Key')
                                                    ->placeholder('pk_live_... / pk_test_...')
                                                    ->autocomplete(false),

                                                TextInput::make('stripe_secret')
                                                    ->label('Secret Key')
                                                    ->password()
                                                    ->revealable()
                                                    ->placeholder('sk_live_... / sk_test_...')
                                                    ->autocomplete(false),
                                            ]),

                                            Grid::make(2)->schema([
                                                TextInput::make('stripe_webhook_secret')
                                                    ->label('Webhook Signing Secret')
                                                    ->password()
                                                    ->revealable()
                                                    ->placeholder('whsec_...')
                                                    ->autocomplete(false),

                                                TextInput::make('_stripe_webhook_url')
                                                    ->label('Webhook Endpoint URL')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->default("{$appUrl}/webhooks/stripe")
                                                    ->helperText('Register this destination URL in Stripe Dashboard > Developers > Webhooks.'),
                                            ]),
                                        ]),
                                ]),

                            Tab::make('Paddle')
                                ->icon('heroicon-o-globe-alt')
                                ->badge(fn () => ! empty($this->data['paddle_enabled']) ? 'Active' : 'Disabled')
                                ->badgeColor(fn () => ! empty($this->data['paddle_enabled']) ? 'success' : 'gray')
                                ->schema([
                                    Section::make('Paddle Gateway (Merchant of Record)')
                                        ->description('Handles global tax, EU VAT, local compliance, and multi-currency billing in 200+ countries.')
                                        ->schema([
                                            Grid::make(2)->schema([
                                                Toggle::make('paddle_enabled')
                                                    ->label('Enable Paddle Gateway')
                                                    ->inline(false)
                                                    ->onColor('success')
                                                    ->offColor('gray'),

                                                Toggle::make('paddle_sandbox')
                                                    ->label('Sandbox Environment')
                                                    ->helperText('Run transactions against the Paddle Sandbox environment.')
                                                    ->inline(false)
                                                    ->onColor('warning')
                                                    ->offColor('gray')
                                                    ->default(true),
                                            ]),

                                            Grid::make(2)->schema([
                                                TextInput::make('paddle_vendor_id')
                                                    ->label('Vendor / Account ID')
                                                    ->placeholder('e.g. 12345'),

                                                TextInput::make('paddle_api_key')
                                                    ->label('API Key (Bearer Token)')
                                                    ->password()
                                                    ->revealable(),
                                            ]),

                                            Grid::make(2)->schema([
                                                TextInput::make('paddle_client_token')
                                                    ->label('Client-Side Token (for overlay/inline checkout)')
                                                    ->placeholder('test_... / live_...'),

                                                TextInput::make('paddle_webhook_secret')
                                                    ->label('Webhook Secret Key')
                                                    ->password()
                                                    ->revealable(),
                                            ]),

                                            TextInput::make('_paddle_webhook_url')
                                                ->label('Webhook Endpoint URL')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->default("{$appUrl}/webhooks/paddle")
                                                ->helperText('Configure this destination URL in Paddle Billing > Notifications.'),
                                        ]),
                                ]),

                            Tab::make('Paystack')
                                ->icon('heroicon-o-currency-dollar')
                                ->badge(fn () => ! empty($this->data['paystack_enabled']) ? 'Active' : 'Disabled')
                                ->badgeColor(fn () => ! empty($this->data['paystack_enabled']) ? 'success' : 'gray')
                                ->schema([
                                    Section::make('Paystack Gateway (Africa)')
                                        ->description('Payment rail optimized for African currencies (NGN, GHS, KES, ZAR, XOF).')
                                        ->schema([
                                            Toggle::make('paystack_enabled')
                                                ->label('Enable Paystack Gateway')
                                                ->inline(false)
                                                ->onColor('success')
                                                ->offColor('gray'),

                                            Grid::make(2)->schema([
                                                TextInput::make('paystack_public_key')
                                                    ->label('Public Key')
                                                    ->placeholder('pk_live_... / pk_test_...'),

                                                TextInput::make('paystack_secret_key')
                                                    ->label('Secret Key')
                                                    ->password()
                                                    ->revealable()
                                                    ->placeholder('sk_live_... / sk_test_...'),
                                            ]),

                                            Grid::make(2)->schema([
                                                TextInput::make('paystack_webhook_secret')
                                                    ->label('Webhook Secret Key')
                                                    ->password()
                                                    ->revealable(),

                                                TextInput::make('_paystack_webhook_url')
                                                    ->label('Webhook Endpoint URL')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->default("{$appUrl}/webhooks/paystack")
                                                    ->helperText('Configure callback in Paystack Dashboard > Settings > API Keys & Webhooks.'),
                                            ]),
                                        ]),
                                ]),

                            Tab::make('Razorpay')
                                ->icon('heroicon-o-bolt')
                                ->badge(fn () => ! empty($this->data['razorpay_enabled']) ? 'Active' : 'Disabled')
                                ->badgeColor(fn () => ! empty($this->data['razorpay_enabled']) ? 'success' : 'gray')
                                ->schema([
                                    Section::make('Razorpay Gateway (India & South Asia)')
                                        ->description('Supports UPI, RuPay, NetBanking, and credit/debit cards in Indian Rupee (INR).')
                                        ->schema([
                                            Toggle::make('razorpay_enabled')
                                                ->label('Enable Razorpay Gateway')
                                                ->inline(false)
                                                ->onColor('success')
                                                ->offColor('gray'),

                                            Grid::make(2)->schema([
                                                TextInput::make('razorpay_key_id')
                                                    ->label('Key ID')
                                                    ->placeholder('rzp_live_... / rzp_test_...'),

                                                TextInput::make('razorpay_key_secret')
                                                    ->label('Key Secret')
                                                    ->password()
                                                    ->revealable(),
                                            ]),

                                            Grid::make(2)->schema([
                                                TextInput::make('razorpay_webhook_secret')
                                                    ->label('Webhook Secret')
                                                    ->password()
                                                    ->revealable(),

                                                TextInput::make('_razorpay_webhook_url')
                                                    ->label('Webhook Endpoint URL')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->default("{$appUrl}/webhooks/razorpay")
                                                    ->helperText('Add webhook in Razorpay Dashboard > Settings > Webhooks.'),
                                            ]),
                                        ]),
                                ]),

                            Tab::make('Mercado Pago')
                                ->icon('heroicon-o-shopping-bag')
                                ->badge(fn () => ! empty($this->data['mercadopago_enabled']) ? 'Active' : 'Disabled')
                                ->badgeColor(fn () => ! empty($this->data['mercadopago_enabled']) ? 'success' : 'gray')
                                ->schema([
                                    Section::make('Mercado Pago Gateway (Latin America)')
                                        ->description('Supports PIX, Boleto, and regional credit/debit cards across Brazil, Mexico, Argentina, Colombia, and Chile.')
                                        ->schema([
                                            Toggle::make('mercadopago_enabled')
                                                ->label('Enable Mercado Pago Gateway')
                                                ->inline(false)
                                                ->onColor('success')
                                                ->offColor('gray'),

                                            Grid::make(2)->schema([
                                                TextInput::make('mercadopago_public_key')
                                                    ->label('Public Key')
                                                    ->placeholder('APP_USR-... / TEST-...'),

                                                TextInput::make('mercadopago_access_token')
                                                    ->label('Access Token')
                                                    ->password()
                                                    ->revealable()
                                                    ->placeholder('APP_USR-...'),
                                            ]),

                                            Grid::make(2)->schema([
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
                                            ]),
                                        ]),
                                ]),

                            Tab::make('PayPal')
                                ->icon('heroicon-o-arrow-path-rounded-square')
                                ->badge(fn () => ! empty($this->data['paypal_enabled']) ? 'Active' : 'Disabled')
                                ->badgeColor(fn () => ! empty($this->data['paypal_enabled']) ? 'success' : 'gray')
                                ->schema([
                                    Section::make('PayPal Gateway (Worldwide)')
                                        ->description('Global PayPal wallets, Pay in 4, and card checkout.')
                                        ->schema([
                                            Grid::make(2)->schema([
                                                Toggle::make('paypal_enabled')
                                                    ->label('Enable PayPal Gateway')
                                                    ->inline(false)
                                                    ->onColor('success')
                                                    ->offColor('gray'),

                                                Toggle::make('paypal_sandbox')
                                                    ->label('Sandbox Mode')
                                                    ->helperText('Run transactions against the PayPal Sandbox environment.')
                                                    ->inline(false)
                                                    ->onColor('warning')
                                                    ->offColor('gray')
                                                    ->default(true),
                                            ]),

                                            Grid::make(2)->schema([
                                                TextInput::make('paypal_client_id')
                                                    ->label('Client ID')
                                                    ->placeholder('e.g. A21AA...'),

                                                TextInput::make('paypal_client_secret')
                                                    ->label('Client Secret')
                                                    ->password()
                                                    ->revealable(),
                                            ]),

                                            Grid::make(2)->schema([
                                                TextInput::make('paypal_webhook_id')
                                                    ->label('Webhook ID')
                                                    ->placeholder('e.g. 9AB...'),

                                                TextInput::make('_paypal_webhook_url')
                                                    ->label('Webhook Endpoint URL')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->default("{$appUrl}/webhooks/paypal")
                                                    ->helperText('Add this webhook in PayPal Developer Dashboard > My Apps & Credentials.'),
                                            ]),
                                        ]),
                                ]),
                        ]),
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
