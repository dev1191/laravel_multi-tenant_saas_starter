<?php

declare(strict_types=1);

namespace App\Domain\Teams\Services;

use App\Domain\Teams\Events\TeamInviteAccepted;
use App\Domain\Teams\Jobs\SendTeamInvitationJob;
use App\Domain\Teams\Models\Team;
use App\Domain\Teams\Models\TeamInvite;
use App\Enums\TenantRole;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class TeamService
{
    /**
     * Get roster of members with their team role & metadata.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getMembersWithRoles(Team $team): Collection
    {
        return $team->members()->get()->map(function (User $member) use ($team) {
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
    }

    /**
     * Get pending active invitations for the team.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getPendingInvites(Team $team): Collection
    {
        return $team->invites()
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
    }

    /**
     * Get invitable role options with fallback to TenantRole enum.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getAvailableRoles(): Collection
    {
        $roles = Role::where('name', '!=', TenantRole::Owner->value)
            ->orderBy('level', 'desc')
            ->get(['id', 'name', 'level']);

        if ($roles->isEmpty()) {
            return collect(TenantRole::cases())
                ->filter(fn (TenantRole $role) => $role !== TenantRole::Owner)
                ->map(fn (TenantRole $role) => [
                    'id' => null,
                    'name' => $role->value,
                    'level' => match ($role) {
                        TenantRole::Admin => 80,
                        TenantRole::Manager => 60,
                        TenantRole::Member => 40,
                        TenantRole::Viewer => 20,
                        default => 0,
                    },
                ])
                ->values();
        }

        return $roles;
    }

    /**
     * Get allowed invitable role names for validation.
     *
     * @return array<string>
     */
    public function getAllowedRoleNames(): array
    {
        $names = Role::where('name', '!=', TenantRole::Owner->value)->pluck('name')->toArray();

        return ! empty($names) ? $names : TenantRole::invitableValues();
    }

    /**
     * Invite a teammate or add directly if user already exists.
     *
     * @return array{status: string, message: string, invite?: TeamInvite}
     */
    public function inviteOrAddMember(Team $team, User $inviter, string $email, string $role): array
    {
        $existingMember = $team->members()->where('email', $email)->first();
        if ($existingMember) {
            return [
                'status' => 'error',
                'message' => __('messages.teams.already_member') ?: 'This user is already a member of the workspace team.',
            ];
        }

        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $team->members()->syncWithoutDetaching([
                $existingUser->id => ['joined_at' => now()],
            ]);
            $existingUser->assignTeamRole($role, $team);

            return [
                'status' => 'added',
                'message' => __('messages.teams.added_success', ['name' => $existingUser->name]) ?: "{$existingUser->name} has been added to the team.",
            ];
        }

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

        return [
            'status' => 'invited',
            'message' => __('messages.teams.invitation_sent') ?: 'Invitation link generated and ready to share.',
            'invite' => $invite,
        ];
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Team $team, User $actor, User $member): void
    {
        if (! $actor->hasRoleLevel(80, $team)) {
            abort(403, 'Unauthorized.');
        }

        if ($member->id === $team->owner_id) {
            abort(403, __('messages.teams.owner_cannot_be_removed') ?: 'The workspace owner cannot be removed.');
        }

        $team->members()->detach($member->id);
    }

    /**
     * Revoke a pending invitation.
     */
    public function revokeInvite(Team $team, User $actor, TeamInvite $invite): void
    {
        if (! $actor->hasRoleLevel(80, $team)) {
            abort(403, 'Unauthorized.');
        }

        $invite->delete();
    }

    /**
     * Accept an invitation for a given user.
     */
    public function acceptInvite(TeamInvite $invite, User $user): void
    {
        $invite->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $invite->team->members()->syncWithoutDetaching([
            $user->id => ['joined_at' => now()],
        ]);

        $user->assignTeamRole($invite->role, $invite->team);

        event(new TeamInviteAccepted($invite->team, $user, $invite));
    }
}
