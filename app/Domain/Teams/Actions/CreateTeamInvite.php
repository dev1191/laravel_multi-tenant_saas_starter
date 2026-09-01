<?php

namespace App\Domain\Teams\Actions;

use App\Domain\Teams\Jobs\SendTeamInvitationJob;
use App\Domain\Teams\Models\Team;
use App\Domain\Teams\Models\TeamInvite;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTeamInvite
{
    use AsAction;

    public function handle(Team $team, User $inviter, string $email, string $role): TeamInvite
    {
        $invite = TeamInvite::create([
            'team_id' => $team->id,
            'email' => $email,
            'role' => $role,
            'token' => TeamInvite::generateToken(),
            'invited_by' => $inviter->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(config('domains.teams.invite_expiration_days', 7)),
        ]);

        SendTeamInvitationJob::dispatch($invite);

        return $invite;
    }

    public function asController(Request $request)
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::firstOrFail();

        if (! $user->hasRoleLevel(80, $team)) {
            abort(403, 'Only Admins or Workspace Owners can invite team members.');
        }

        if (Feature::active('team-invites') === false) {
            abort(403, 'Team invites are not supported on your current subscription plan.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'string', 'in:admin,manager,member,viewer'],
        ]);

        $existingMember = $team->members()->where('email', $validated['email'])->first();
        if ($existingMember) {
            return back()->withErrors(['email' => 'This user is already a member of the workspace team.']);
        }

        $existingUser = User::where('email', $validated['email'])->first();

        if ($existingUser) {
            $team->members()->syncWithoutDetaching([
                $existingUser->id => ['joined_at' => now()],
            ]);
            $existingUser->assignTeamRole($validated['role'], $team);

            return back()->with('success', "{$existingUser->name} has been added to the team.");
        }

        $this->handle($team, $user, $validated['email'], $validated['role']);

        return back()->with('success', 'Invitation link generated and ready to share.');
    }
}
