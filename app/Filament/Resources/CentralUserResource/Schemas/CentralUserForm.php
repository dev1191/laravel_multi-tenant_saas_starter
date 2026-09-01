<?php

namespace App\Filament\Resources\CentralUserResource\Schemas;

use App\Domain\TenantAdmin\Models\CentralUser;
use App\Enums\CentralUserRole;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CentralUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('messages.staff.account') ?: 'Staff Account')->schema([
                FileUpload::make('avatar_url')
                    ->label(__('messages.staff.avatar') ?: 'Avatar')
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->image()
                    ->avatar()
                    ->circleCropper()
                    ->maxSize(2048)
                    ->columnSpanFull(),

                TextInput::make('name')
                    ->label(__('messages.staff.name') ?: 'Full Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label(__('messages.staff.email') ?: 'Email Address')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(CentralUser::class, 'email', ignoreRecord: true),

                TextInput::make('password')
                    ->label(__('messages.staff.password') ?: 'Password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->helperText(fn (string $context): ?string => $context === 'edit' ? (__('messages.staff.password_help') ?: 'Leave blank to keep the current password.') : null),

                Select::make('role')
                    ->label(__('messages.staff.role') ?: 'Role')
                    ->options(CentralUserRole::class)
                    ->default(CentralUserRole::Owner->value)
                    ->required(),
            ])->columns(2),
        ]);
    }
}
