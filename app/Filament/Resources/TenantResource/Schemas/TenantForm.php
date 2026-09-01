<?php

namespace App\Filament\Resources\TenantResource\Schemas;

use App\Domain\Billing\Models\Plan;
use App\Enums\TenantStatus;
use App\Models\Language;
use App\Models\Tenant;
use App\Support\Country;
use App\Support\Currency;
use App\Support\Timezone;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        $centralDomain = config('tenancy.central_domains.0', 'tenantforge.test');

        return $schema
            ->columns([
                'default' => 1,
                'lg' => 3,
            ])
            ->schema([
                // Main Content (2 Columns wide)
                Group::make([
                    Section::make(__('messages.tenant.workspace_details'))
                        ->description('Core workspace identity, custom subdomain, and primary contact information.')
                        ->icon('heroicon-o-building-office-2')
                        ->schema([
                            TextInput::make('name')
                                ->label(__('messages.tenant.workspace_name'))
                                ->placeholder('e.g. Acme Corporation')
                                ->prefixIcon('heroicon-m-building-office')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('email')
                                ->label(__('messages.auth.email'))
                                ->placeholder('admin@example.com')
                                ->prefixIcon('heroicon-m-envelope')
                                ->email()
                                ->required()
                                ->maxLength(255),

                            TextInput::make('id')
                                ->label(__('messages.tenant.subdomain').' (Slug)')
                                ->placeholder('acme')
                                ->prefix('https://')
                                ->suffix('.'.$centralDomain)
                                ->required()
                                ->disabledOn('edit')
                                ->unique(Tenant::class, 'id', ignoreRecord: true)
                                ->helperText('Subdomain identifier used to access this workspace.')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Section::make('Workspace Branding & Logos')
                        ->description('Light and dark mode brand logos used in tenant headers and documents.')
                        ->icon('heroicon-o-sparkles')
                        ->collapsible()
                        ->schema([
                            FileUpload::make('logo_light_path')
                                ->label('Light Mode Logo')
                                ->disk('public')
                                ->directory('tenant-logos')
                                ->visibility('public')
                                ->image()
                                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                                ->maxSize(5120)
                                ->helperText('Displayed against light backgrounds.'),

                            FileUpload::make('logo_dark_path')
                                ->label('Dark Mode Logo')
                                ->disk('public')
                                ->directory('tenant-logos')
                                ->visibility('public')
                                ->image()
                                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                                ->maxSize(5120)
                                ->helperText('Displayed against dark backgrounds.'),
                        ])
                        ->columns(2),

                    Section::make(__('messages.settings.localization'))
                        ->description('Configure regional currency, language interface, country, and timezone defaults.')
                        ->icon('heroicon-o-globe-alt')
                        ->schema([
                            Select::make('country_code')
                                ->label(__('messages.settings.country'))
                                ->options(Country::options())
                                ->prefixIcon('heroicon-m-globe-americas')
                                ->searchable()
                                ->placeholder(__('messages.settings.country')),

                            Select::make('default_currency')
                                ->label(__('messages.settings.currency'))
                                ->options(Currency::options())
                                ->prefixIcon('heroicon-m-banknotes')
                                ->searchable()
                                ->default('USD')
                                ->required(),

                            Select::make('default_locale')
                                ->label(__('messages.settings.language').' (Locale)')
                                ->options(fn () => Language::orderBy('display_order')->get()->mapWithKeys(fn ($lang) => [
                                    $lang->code => $lang->flag ? "{$lang->flag} {$lang->name} ({$lang->code})" : "{$lang->name} ({$lang->code})",
                                ])->toArray())
                                ->prefixIcon('heroicon-m-language')
                                ->searchable()
                                ->default('en')
                                ->required(),

                            Select::make('timezone')
                                ->label(__('messages.settings.timezone'))
                                ->options(Timezone::options())
                                ->prefixIcon('heroicon-m-clock')
                                ->searchable()
                                ->default('UTC')
                                ->required(),
                        ])
                        ->columns(2),

                    Section::make('Tax & Compliance Infrastructure')
                        ->description('Manage tax identifiers, exemption rules, and optional regional database residency.')
                        ->icon('heroicon-o-server-stack')
                        ->collapsible()
                        ->collapsed(fn (string $operation): bool => $operation === 'create')
                        ->schema([
                            TextInput::make('tax_id')
                                ->label(__('messages.tenant.tax_id'))
                                ->placeholder('e.g. US123456789 / GB999999973')
                                ->prefixIcon('heroicon-m-receipt-percent'),

                            Toggle::make('tax_exempt')
                                ->label(__('messages.tenant.tax_exempt'))
                                ->helperText('Exempt this workspace from automatic VAT/sales tax calculations.')
                                ->default(false),

                            TextInput::make('db_host')
                                ->label(__('messages.tenant.db_host'))
                                ->placeholder('e.g. eu-west-1.db.tenantforge.internal')
                                ->prefixIcon('heroicon-m-server')
                                ->helperText(__('messages.tenant.db_host_helper'))
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                ])
                ->columnSpan([
                    'default' => 1,
                    'lg' => 2,
                ]),

                // Sidebar Content (1 Column wide)
                Group::make([
                    Section::make('Lifecycle & Subscription')
                        ->description('Control tenant active status, tier, and trial period.')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Select::make('status')
                                ->label(__('messages.common.status'))
                                ->options(TenantStatus::class)
                                ->default(TenantStatus::Trial)
                                ->prefixIcon('heroicon-m-signal')
                                ->required(),

                            Select::make('plan')
                                ->label(__('messages.billing.current_plan'))
                                ->options(fn () => Plan::pluck('name', 'slug')->toArray() ?: [
                                    'trial' => 'Trial',
                                    'starter' => 'Starter',
                                    'pro' => 'Pro',
                                    'business' => 'Business',
                                ])
                                ->prefixIcon('heroicon-m-sparkles')
                                ->default('trial')
                                ->required(),

                            Flatpickr::make('trial_ends_at')
                                ->label(__('messages.tenant.trial_ends_at'))
                                ->enableTime()
                                ->dateFormat('Y-m-d H:i:s')
                                ->displayFormat('M j, Y H:i')
                                ->prefixIcon('heroicon-m-calendar-days')
                                ->default(now()->addDays(14))
                                ->helperText('Date when workspace trial expires.'),
                        ]),
                ])
                ->columnSpan([
                    'default' => 1,
                    'lg' => 1,
                ]),
            ]);
    }
}
