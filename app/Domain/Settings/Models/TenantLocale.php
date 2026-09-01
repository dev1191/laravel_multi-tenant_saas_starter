<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantLocale extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'direction',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }
}
