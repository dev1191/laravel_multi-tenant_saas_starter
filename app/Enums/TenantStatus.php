<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TenantStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Trial = 'trial';
    case Suspended = 'suspended';
    case Provisioning = 'provisioning';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Active => __('messages.common.active') ?? 'Active',
            self::Trial => 'Trial',
            self::Suspended => 'Suspended',
            self::Provisioning => 'Provisioning',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Trial => 'warning',
            self::Suspended => 'danger',
            self::Provisioning => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-o-check-circle',
            self::Trial => 'heroicon-o-clock',
            self::Suspended => 'heroicon-o-no-symbol',
            self::Provisioning => 'heroicon-o-arrow-path',
        };
    }
}
