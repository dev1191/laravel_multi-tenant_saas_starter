<?php

namespace App\Domain\Teams\Controllers;

use App\Domain\Teams\Models\Team;
use App\Domain\Teams\Models\TeamInvite;
use App\Domain\Teams\Services\TeamService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Pennant\Feature;

class TeamController extends Controller
{
    public function __construct(
        protected TeamService $teamService,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $selectedTeamId = $request->query('team_id');
        $team = ($selectedTeamId ? $user->teams()->find($selectedTeamId) : null)
            ?? $user->currentTeam()
            ?? Team::firstOrFail();

        $canManage = $user->hasRoleLevel(80, $team);
        $userTeams = $user->teams()->withCount('members')->get(['teams.id', 'teams.name', 'teams.slug']);

        return Inertia::render('Teams/Index', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'owner_id' => $team->owner_id,
            ],
            'user_teams' => $userTeams,
            'members' => $this->teamService->getMembersWithRoles($team),
            'invites' => $this->teamService->getPendingInvites($team),
            'available_roles' => $this->teamService->getAvailableRoles(),
            'can_manage' => $canManage,
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::firstOrFail();

        if (! $user->hasRoleLevel(80, $team)) {
            abort(403, __('messages.teams.only_admins_can_invite') ?: 'Only Admins or Workspace Owners can invite team members.');
        }

        if (Feature::active('team-invites') === false) {
            abort(403, __('messages.teams.invites_not_supported_on_plan') ?: 'Team invites are not supported on your current subscription plan.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'string', Rule::in($this->teamService->getAllowedRoleNames())],
        ]);

        $result = $this->teamService->inviteOrAddMember($team, $user, $validated['email'], $validated['role']);

        if ($result['status'] === 'error') {
            return back()->withErrors(['email' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    public function removeMember(Request $request, User $member): RedirectResponse
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::firstOrFail();

        $this->teamService->removeMember($team, $user, $member);

        return back()->with('success', __('messages.teams.removed_success', ['name' => $member->name]) ?: "{$member->name} removed from the team.");
    }

    public function removeInvite(Request $request, TeamInvite $invite): RedirectResponse
    {
        $user = $request->user();
        $this->teamService->revokeInvite($invite->team, $user, $invite);

        return back()->with('success', __('messages.teams.revoked_success') ?: 'Invitation has been revoked.');
    }
}
