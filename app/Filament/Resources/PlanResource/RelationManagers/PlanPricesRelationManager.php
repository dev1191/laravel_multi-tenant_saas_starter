<?php

namespace App\Filament\Resources\PlanResource\RelationManagers;

use App\Support\Currency;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlanPricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    protected static ?string $title = 'Multi-Currency Gateway Pricing';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('currency')
                ->options(Currency::options())
                ->searchable()
                ->default('USD')
                ->required(),

            TextInput::make('amount')
                ->label('Amount (in smallest unit, e.g. 2900 for $29.00)')
                ->numeric()
                ->required()
                ->helperText('2900 = $29.00, 9900 = $99.00'),

            Select::make('gateway')
                ->options([
                    'stripe' => 'Stripe',
                    'paddle' => 'Paddle',
                    'paystack' => 'Paystack',
                    'razorpay' => 'Razorpay',
                    'mercadopago' => 'MercadoPago',
                    'paypal' => 'PayPal',
                ])
                ->default('stripe')
                ->required(),

            TextInput::make('gateway_price_id')
                ->label('Gateway Price ID (e.g. price_xxx, plan_xxx)')
                ->placeholder('price_1Nx...'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('currency')
                    ->badge()
                    ->sortable(),
                TextColumn::make('formatted_amount')
                    ->label('Price')
                    ->sortable(),
                TextColumn::make('gateway')
                    ->badge()
                    ->colors([
                        'primary' => 'stripe',
                        'info' => 'paddle',
                        'success' => 'paystack',
                        'warning' => 'razorpay',
                        'danger' => 'mercadopago',
                        'secondary' => 'paypal',
                    ]),
                TextColumn::make('gateway_price_id')
                    ->label('Gateway ID')
                    ->copyable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
