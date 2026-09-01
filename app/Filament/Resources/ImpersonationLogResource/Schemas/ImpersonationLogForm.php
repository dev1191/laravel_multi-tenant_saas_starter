<?php

namespace App\Filament\Resources\ImpersonationLogResource\Schemas;

use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ImpersonationLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Audit Details')->schema([
                TextInput::make('token')->disabled(),
                TextInput::make('central_user_id')
                    ->label('Staff Member')
                    ->formatStateUsing(fn ($record) => $record?->centralUser?->name." ({$record?->centralUser?->email})")
                    ->disabled(),
                TextInput::make('tenant_id')
                    ->label('Tenant Workspace')
                    ->formatStateUsing(fn ($record) => $record?->tenant?->name." ({$record?->tenant_id})")
                    ->disabled(),
                TextInput::make('ip_address')->label('IP Address')->disabled(),
                TextInput::make('user_agent')->label('User Agent')->disabled(),
                Flatpickr::make('started_at')
                    ->label('Started At')
                    ->enableTime()
                    ->disabled(),
                Flatpickr::make('ended_at')
                    ->label('Ended At')
                    ->enableTime()
                    ->disabled(),
            ])->columns(2),
        ]);
    }
}
