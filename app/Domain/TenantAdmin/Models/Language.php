<?php

declare(strict_types=1);

namespace App\Domain\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Language extends Model
{
    use CentralConnection, HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'direction',
        'flag',
        'is_active',
        'is_default',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    public function isLtr(): bool
    {
        return $this->direction !== 'rtl';
    }
}
