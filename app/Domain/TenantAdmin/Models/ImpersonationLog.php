<?php

namespace App\Domain\TenantAdmin\Models;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class ImpersonationLog extends Model
{
    use CentralConnection, HasFactory;

    protected $fillable = [
        'token',
        'central_user_id',
        'tenant_id',
        'impersonated_user_id',
        'ip_address',
        'user_agent',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function centralUser(): BelongsTo
    {
        return $this->belongsTo(CentralUser::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
