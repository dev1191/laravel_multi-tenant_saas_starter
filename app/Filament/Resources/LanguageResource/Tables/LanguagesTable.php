<?php

declare(strict_types=1);

namespace App\Filament\Resources\LanguageResource\Tables;

use App\Domain\TenantAdmin\Models\Language;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LanguagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_order')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('flag')
                    ->label('')
                    ->width('40px'),
                TextColumn::make('name')
                    ->label(__('messages.language.language'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (Language $record) => $record->native_name),
                TextColumn::make('code')
                    ->label(__('messages.language.code'))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('direction')
                    ->label(__('messages.language.direction'))
                    ->badge()
                    ->colors([
                        'info' => 'ltr',
                        'warning' => 'rtl',
                    ])
                    ->formatStateUsing(fn (string $state) => strtoupper($state)),
                IconColumn::make('is_default')
                    ->label(__('messages.common.default'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('messages.common.active'))
                    ->boolean(),
            ])
            ->defaultSort('display_order', 'asc')
            ->filters([
                SelectFilter::make('direction')
                    ->options([
                        'ltr' => 'LTR',
                        'rtl' => 'RTL',
                    ]),
                SelectFilter::make('is_active')
                    ->label(__('messages.common.status'))
                    ->options([
                        '1' => __('messages.common.active'),
                        '0' => __('messages.common.inactive'),
                    ]),
            ])
            ->actions([
                Action::make('toggle_active')
                    ->label(fn (Language $record) => $record->is_active ? __('messages.common.inactive') : __('messages.common.active'))
                    ->icon(fn (Language $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Language $record) => $record->is_active ? 'danger' : 'success')
                    ->action(function (Language $record) {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title(__('messages.language.status_updated'))
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
