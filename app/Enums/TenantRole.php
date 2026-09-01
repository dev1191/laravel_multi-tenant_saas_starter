<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TenantRole: string implements HasColor, HasIcon, HasLabel
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Manager = 'manager';
    case Member = 'member';
    case Viewer = 'viewer';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Owner => __('messages.teams.owner') ?: __('messages.roles.owner') ?: 'Owner',
            self::Admin => __('messages.teams.admin') ?: __('messages.roles.admin') ?: 'Admin',
            self::Manager => __('messages.roles.manager') ?: 'Manager',
            self::Member => __('messages.teams.member') ?: __('messages.roles.member') ?: 'Member',
            self::Viewer => __('messages.roles.viewer') ?: 'Viewer',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Owner => 'danger',
            self::Admin => 'primary',
            self::Manager => 'warning',
            self::Member => 'success',
            self::Viewer => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Owner => 'heroicon-o-key',
            self::Admin => 'heroicon-o-shield-check',
            self::Manager => 'heroicon-o-user-group',
            self::Member => 'heroicon-o-user',
            self::Viewer => 'heroicon-o-eye',
        };
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Roles eligible for invitations (excluding workspace owner).
     *
     * @return array<string>
     */
    public static function invitableValues(): array
    {
        return [
            self::Admin->value,
            self::Manager->value,
            self::Member->value,
            self::Viewer->value,
        ];
    }
}
