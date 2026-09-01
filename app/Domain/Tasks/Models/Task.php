<?php

namespace App\Domain\Tasks\Models;

use App\Domain\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Task extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'assigned_to',
        'created_by',
        'team_id',
        'due_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'priority', 'assigned_to'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
