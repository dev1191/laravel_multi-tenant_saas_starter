<?php

namespace App\Filament\Resources\PlanResource\Schemas;

use App\Domain\Billing\Models\Plan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Plan Configuration')->schema([
                TextInput::make('name')
                    ->label('Plan Name (e.g. Starter, Pro, Business)')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(Plan::class, 'slug', ignoreRecord: true),
                Select::make('billing_period')
                    ->options([
                        'monthly' => 'Monthly',
                        'yearly' => 'Yearly',
                    ])
                    ->default('monthly')
                    ->required(),
                Toggle::make('is_active')
                    ->label('Active for New Signups')
                    ->default(true),
            ])->columns(2),

            Section::make('Feature Flags (Pennant Gating)')->schema([
                TagsInput::make('features')
                    ->label('Feature Flags Enabled in this Plan')
                    ->placeholder('Add feature (e.g. team-invites, advanced-analytics, custom-branding)')
                    ->helperText('Used by Laravel Pennant to gate tenant features.'),
            ]),
        ]);
    }
}
