<?php

namespace App\Filament\Resources;

use App\Domain\TenantAdmin\Models\ImpersonationLog;
use App\Filament\Resources\ImpersonationLogResource\Pages;
use App\Filament\Resources\ImpersonationLogResource\Schemas\ImpersonationLogForm;
use App\Filament\Resources\ImpersonationLogResource\Tables\ImpersonationLogsTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ImpersonationLogResource extends Resource
{
    protected static ?string $model = ImpersonationLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Security & Audit';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ImpersonationLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImpersonationLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImpersonationLogs::route('/'),
        ];
    }
}
