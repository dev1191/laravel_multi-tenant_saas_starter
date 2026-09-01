<?php

namespace App\Domain\Billing\Models;

use App\Support\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class PlanPrice extends Model
{
    use CentralConnection, HasFactory;

    protected $fillable = [
        'plan_id',
        'currency',
        'amount',
        'gateway',
        'gateway_price_id',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return Currency::format($this->amount / 100, $this->currency);
    }
}
