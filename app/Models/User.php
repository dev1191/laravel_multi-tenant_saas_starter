<?php

namespace App\Models;

use App\Domain\Teams\Models\Team;
use App\Domain\Teams\Models\TeamInvite;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'locale',
        'timezone',
        'date_format',
        'time_format',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => UserStatus::class,
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user account is active.
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::Active || $this->status === 'active' || $this->status === null;
    }

    /**
     * Check if user account is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === UserStatus::Inactive || $this->status === 'inactive';
    }

    /**
     * Check if user account is pending.
     */
    public function isPending(): bool
    {
        return $this->status === UserStatus::Pending || $this->status === 'pending';
    }

    /**
     * Check if user account is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === UserStatus::Suspended || $this->status === 'suspended';
    }

    /**
     * Teams the user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    /**
     * Teams owned by the user.
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * Team invites sent by the user.
     */
    public function sentInvites(): HasMany
    {
        return $this->hasMany(TeamInvite::class, 'invited_by');
    }

    /**
     * Get the user's primary/active team.
     */
    public function currentTeam(): ?Team
    {
        return $this->teams()->first() ?? $this->ownedTeams()->first();
    }

    /**
     * Check if user has a role with level greater than or equal to $minLevel.
     */
    public function hasRoleLevel(int $minLevel, ?Team $team = null): bool
    {
        $resolvedTeam = $team ?? $this->currentTeam() ?? Team::first();

        // Team/Workspace owner automatically holds highest role level (100)
        if ($resolvedTeam && $resolvedTeam->owner_id === $this->id) {
            return true;
        }

        // If no team exists yet or user is the initial workspace user
        if (! $resolvedTeam) {
            return true;
        }

        $teamId = $resolvedTeam->id;

        $roles = $this->roles();
        if ($teamId) {
            $roles = $roles->wherePivot('team_id', $teamId);
        }

        return $roles->where('level', '>=', $minLevel)->exists();
    }

    /**
     * Highest role level the user holds in a given team.
     */
    public function highestRoleLevel(?Team $team = null): int
    {
        $resolvedTeam = $team ?? $this->currentTeam() ?? Team::first();

        if ($resolvedTeam && $resolvedTeam->owner_id === $this->id) {
            return 100;
        }

        $teamId = $resolvedTeam?->id;

        $query = $this->roles();
        if ($teamId) {
            $query = $query->wherePivot('team_id', $teamId);
        }

        return (int) ($query->max('level') ?? 0);
    }

    /**
     * Assign a Spatie role scoped to a specific workspace team.
     */
    public function assignTeamRole(string $role, ?Team $team = null): self
    {
        $teamId = $team?->id ?? $this->currentTeam()?->id;
        if ($teamId) {
            setPermissionsTeamId($teamId);
        }

        $this->assignRole($role);

        return $this;
    }
}
