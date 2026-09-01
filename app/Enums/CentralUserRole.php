<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CentralUserRole: string implements HasColor, HasIcon, HasLabel
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Support = 'support';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Owner => __('messages.roles.owner') ?: 'Platform Owner',
            self::Admin => __('messages.roles.admin') ?: 'Administrator',
            self::Support => __('messages.roles.support') ?: 'Support Agent',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Owner => 'danger',
            self::Admin => 'warning',
            self::Support => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Owner => 'heroicon-o-shield-check',
            self::Admin => 'heroicon-o-user-group',
            self::Support => 'heroicon-o-lifebuoy',
        };
    }
}
