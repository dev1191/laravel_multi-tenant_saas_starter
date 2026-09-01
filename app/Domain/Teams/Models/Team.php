<?php

namespace App\Domain\Teams\Models;

use App\Domain\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    public function invites(): HasMany
    {
        return $this->hasMany(TeamInvite::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
