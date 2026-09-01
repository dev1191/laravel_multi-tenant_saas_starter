<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\TenantAdmin\Models\Language;
use App\Filament\Resources\LanguageResource\Pages;
use App\Filament\Resources\LanguageResource\Schemas\LanguageForm;
use App\Filament\Resources\LanguageResource\Tables\LanguagesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-language';

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('messages.language.language');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.language.languages');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('messages.settings.title');
    }

    public static function form(Schema $schema): Schema
    {
        return LanguageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LanguagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLanguages::route('/'),
            'create' => Pages\CreateLanguage::route('/create'),
            'edit' => Pages\EditLanguage::route('/{record}/edit'),
        ];
    }
}
