<?php

namespace App\Domain\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Plan extends Model
{
    use CentralConnection, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'billing_period',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
        ];
    }

    public function prices(): HasMany
    {
        return $this->hasMany(PlanPrice::class);
    }

    public function priceForCurrency(string $currency, string $gateway = 'stripe'): ?PlanPrice
    {
        return $this->prices()
            ->where('currency', strtoupper($currency))
            ->where('gateway', $gateway)
            ->first();
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? [], true);
    }
}
