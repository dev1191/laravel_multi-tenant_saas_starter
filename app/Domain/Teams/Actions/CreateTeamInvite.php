<?php

namespace App\Domain\Teams\Actions;

use App\Domain\Teams\Models\Team;
use App\Domain\Teams\Models\TeamInvite;
use App\Domain\Teams\Services\TeamService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Pennant\Feature;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTeamInvite
{
    use AsAction;

    public function handle(Team $team, User $inviter, string $email, string $role): ?TeamInvite
    {
        $result = app(TeamService::class)->inviteOrAddMember($team, $inviter, $email, $role);

        return $result['invite'] ?? null;
    }

    public function asController(Request $request)
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::firstOrFail();

        if (! $user->hasRoleLevel(80, $team)) {
            abort(403, __('messages.teams.only_admins_can_invite') ?: 'Only Admins or Workspace Owners can invite team members.');
        }

        if (Feature::active('team-invites') === false) {
            abort(403, __('messages.teams.invites_not_supported_on_plan') ?: 'Team invites are not supported on your current subscription plan.');
        }

        $service = app(TeamService::class);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'string', Rule::in($service->getAllowedRoleNames())],
        ]);

        $result = $service->inviteOrAddMember($team, $user, $validated['email'], $validated['role']);

        if ($result['status'] === 'error') {
            return back()->withErrors(['email' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }
}
