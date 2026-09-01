<?php

namespace App\Filament\Resources\CentralUserResource\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CentralUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label(__('messages.staff.avatar') ?: 'Avatar')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=4F46E5'),

                TextColumn::make('name')
                    ->label(__('messages.staff.name') ?: 'Full Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('email')
                    ->label(__('messages.staff.email') ?: 'Email Address')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('role')
                    ->label(__('messages.staff.role') ?: 'Role')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label(__('messages.common.created_at') ?: 'Created At')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
