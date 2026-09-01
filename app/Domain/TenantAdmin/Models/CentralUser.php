<?php

namespace App\Domain\TenantAdmin\Models;

use App\Enums\CentralUserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class CentralUser extends Authenticatable implements FilamentUser, HasAvatar
{
    use CentralConnection, HasFactory, Notifiable;

    protected $table = 'central_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => CentralUserRole::class,
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (! $this->avatar_url) {
            return null;
        }

        if (str_starts_with($this->avatar_url, 'http://') || str_starts_with($this->avatar_url, 'https://') || str_starts_with($this->avatar_url, '/')) {
            return $this->avatar_url;
        }

        return Storage::disk('public')->url($this->avatar_url);
    }

    public function isOwner(): bool
    {
        return $this->role === CentralUserRole::Owner || $this->role === 'owner';
    }

    public function isSupport(): bool
    {
        $roleValue = $this->role instanceof CentralUserRole ? $this->role->value : $this->role;

        return in_array($roleValue, ['support', 'admin', 'owner'], true);
    }

    public function impersonationLogs(): HasMany
    {
        return $this->hasMany(ImpersonationLog::class);
    }
}
