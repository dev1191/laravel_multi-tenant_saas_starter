<?php

namespace App\Domain\Teams\Controllers;

use App\Domain\Teams\Models\Team;
use App\Domain\Teams\Models\TeamInvite;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Pennant\Feature;
use Spatie\Permission\Models\Role;

class TeamController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::firstOrFail();

        $canManage = $user->hasRoleLevel(80, $team);

        $members = $team->members()->get()->map(function (User $member) use ($team) {
            $role = $member->roles()->wherePivot('team_id', $team->id)->first();

            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'role' => $role?->name ?? 'member',
                'role_level' => $role?->level ?? 40,
                'joined_at' => $member->pivot?->joined_at ? Carbon::parse($member->pivot->joined_at)->format('M j, Y') : null,
                'is_owner' => $member->id === $team->owner_id,
            ];
        });

        $invites = $team->invites()
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest()
            ->get()
            ->map(function (TeamInvite $invite) {
                return [
                    'id' => $invite->id,
                    'email' => $invite->email,
                    'role' => $invite->role,
                    'invited_by' => $invite->inviter?->name ?? 'Admin',
                    'expires_at' => $invite->expires_at->format('M j, Y'),
                    'invite_url' => url('/invite/'.$invite->token),
                ];
            });

        $availableRoles = Role::orderBy('level', 'desc')->get(['id', 'name', 'level']);

        return Inertia::render('Teams/Index', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'owner_id' => $team->owner_id,
            ],
            'members' => $members,
            'invites' => $invites,
            'available_roles' => $availableRoles,
            'can_manage' => $canManage,
        ]);
    }

    public function invite(Request $request): RedirectResponse
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
            $existingUser->assignRole($validated['role'], $team);

            return back()->with('success', "{$existingUser->name} has been added to the team.");
        }

        $invite = TeamInvite::create([
            'team_id' => $team->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'token' => TeamInvite::generateToken(),
            'invited_by' => $user->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        return back()->with('success', 'Invitation link generated and ready to share.');
    }

    public function removeMember(Request $request, User $member): RedirectResponse
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::firstOrFail();

        if (! $user->hasRoleLevel(80, $team)) {
            abort(403, 'Unauthorized.');
        }

        if ($member->id === $team->owner_id) {
            abort(403, 'The workspace owner cannot be removed.');
        }

        $team->members()->detach($member->id);

        return back()->with('success', "{$member->name} removed from the team.");
    }
}
