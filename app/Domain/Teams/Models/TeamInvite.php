<?php

namespace App\Domain\Teams\Models;

use App\Domain\Teams\Events\TeamInviteAccepted;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeamInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'email',
        'role',
        'token',
        'invited_by',
        'status',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public static function generateToken(): string
    {
        return Str::random(40);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast() || $this->status === 'expired';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    public function accept(User $user): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $this->team->members()->syncWithoutDetaching([
            $user->id => ['joined_at' => now()],
        ]);

        // Assign role scoped to team in Spatie Permissions
        $user->assignRole($this->role, $this->team);

        // Fire cross-domain decoupled event
        event(new TeamInviteAccepted($this->team, $user, $this));
    }
}
