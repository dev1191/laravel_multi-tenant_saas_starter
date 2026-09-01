<?php

namespace App\Filament\Resources;

use App\Domain\TenantAdmin\Models\CentralUser;
use App\Filament\Resources\CentralUserResource\Pages;
use App\Filament\Resources\CentralUserResource\Schemas\CentralUserForm;
use App\Filament\Resources\CentralUserResource\Tables\CentralUsersTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CentralUserResource extends Resource
{
    protected static ?string $model = CentralUser::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Email' => $record->email,
            'Role' => $record->role instanceof \App\Enums\CentralUserRole ? $record->role->getLabel() : (string) $record->role,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('messages.settings.title') ?: 'Settings';
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.staff.title') ?: 'Staff Accounts';
    }

    public static function getModelLabel(): string
    {
        return __('messages.staff.account') ?: 'Staff Account';
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.staff.title') ?: 'Staff Accounts';
    }

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return CentralUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CentralUsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCentralUsers::route('/'),
            'create' => Pages\CreateCentralUser::route('/create'),
            'edit' => Pages\EditCentralUser::route('/{record}/edit'),
        ];
    }
}
