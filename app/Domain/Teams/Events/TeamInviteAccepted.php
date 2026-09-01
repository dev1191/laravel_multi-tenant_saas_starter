<?php

namespace App\Domain\Teams\Events;

use App\Domain\Teams\Models\Team;
use App\Domain\Teams\Models\TeamInvite;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamInviteAccepted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Team $team,
        public User $user,
        public TeamInvite $invite
    ) {}
}
