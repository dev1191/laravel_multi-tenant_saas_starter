<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UserStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
    case Suspended = 'suspended';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Active => __('messages.common.active') ?? 'Active',
            self::Inactive => __('messages.common.inactive') ?? 'Inactive',
            self::Pending => 'Pending',
            self::Suspended => 'Suspended',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'gray',
            self::Pending => 'warning',
            self::Suspended => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-o-check-circle',
            self::Inactive => 'heroicon-o-minus-circle',
            self::Pending => 'heroicon-o-clock',
            self::Suspended => 'heroicon-o-no-symbol',
        };
    }
}
