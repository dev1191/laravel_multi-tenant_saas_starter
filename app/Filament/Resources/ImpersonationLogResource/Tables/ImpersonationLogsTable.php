<?php

namespace App\Filament\Resources\ImpersonationLogResource\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImpersonationLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('centralUser.name')
                    ->label('Staff User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tenant.name')
                    ->label('Impersonated Tenant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->copyable(),
                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime('M j, Y H:i:s')
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->label('Ended')
                    ->dateTime('M j, Y H:i:s')
                    ->placeholder('Active / In Progress')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => $state !== null,
                        'warning' => fn ($state) => $state === null,
                    ]),
            ])
            ->defaultSort('started_at', 'desc');
    }
}
