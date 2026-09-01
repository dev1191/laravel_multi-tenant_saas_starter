<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\Schemas\TenantForm;
use App\Filament\Resources\TenantResource\Tables\TenantsTable;
use App\Models\Tenant;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('messages.tenant.workspace');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.tenant.workspaces');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('messages.tenant.workspace').' Management';
    }

    public static function form(Schema $schema): Schema
    {
        return TenantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
