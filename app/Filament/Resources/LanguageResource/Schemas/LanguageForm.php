<?php

declare(strict_types=1);

namespace App\Filament\Resources\LanguageResource\Schemas;

use App\Domain\TenantAdmin\Models\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LanguageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('messages.language.management'))->schema([
                TextInput::make('code')
                    ->label(__('messages.language.code'))
                    ->placeholder('e.g. en, es, ar, pt_BR')
                    ->required()
                    ->maxLength(10)
                    ->unique(Language::class, 'code', ignoreRecord: true),
                TextInput::make('name')
                    ->label(__('messages.language.name'))
                    ->placeholder('e.g. Spanish')
                    ->required()
                    ->maxLength(255),
                TextInput::make('native_name')
                    ->label(__('messages.language.native_name'))
                    ->placeholder('e.g. Español')
                    ->maxLength(255),
                Select::make('direction')
                    ->label(__('messages.language.direction'))
                    ->options([
                        'ltr' => __('messages.language.ltr'),
                        'rtl' => __('messages.language.rtl'),
                    ])
                    ->default('ltr')
                    ->required(),
                TextInput::make('flag')
                    ->label(__('messages.language.flag'))
                    ->placeholder('e.g. 🇪🇸')
                    ->maxLength(10),
                TextInput::make('display_order')
                    ->label(__('messages.language.display_order'))
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('messages.language.is_active'))
                    ->default(true),
                Toggle::make('is_default')
                    ->label(__('messages.language.is_default'))
                    ->default(false),
            ])->columns(2),
        ]);
    }
}
