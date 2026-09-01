<?php

namespace App\Domain\Teams\Actions;

use App\Domain\Teams\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveTeamMember
{
    use AsAction;

    public function handle(Team $team, User $member): void
    {
        $team->members()->detach($member->id);
    }

    public function asController(Request $request, User $member)
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::firstOrFail();

        if (! $user->hasRoleLevel(80, $team)) {
            abort(403, 'Unauthorized.');
        }

        if ($member->id === $team->owner_id) {
            abort(403, 'The workspace owner cannot be removed.');
        }

        $this->handle($team, $member);

        return back()->with('success', "{$member->name} removed from the team.");
    }
}
