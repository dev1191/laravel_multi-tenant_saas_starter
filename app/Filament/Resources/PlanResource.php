<?php

namespace App\Filament\Resources;

use App\Domain\Billing\Models\Plan;
use App\Filament\Resources\PlanResource\Pages;
use App\Filament\Resources\PlanResource\RelationManagers\PlanPricesRelationManager;
use App\Filament\Resources\PlanResource\Schemas\PlanForm;
use App\Filament\Resources\PlanResource\Tables\PlansTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 2;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'stripe_product_id'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Slug' => $record->slug,
            'Billing Period' => $record->billing_period,
        ];
    }

    public static function getModelLabel(): string
    {
        return __('messages.billing.current_plan');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.billing.pricing');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('messages.billing.title');
    }

    public static function form(Schema $schema): Schema
    {
        return PlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PlanPricesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
