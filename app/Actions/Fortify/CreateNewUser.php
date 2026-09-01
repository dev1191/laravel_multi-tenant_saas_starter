<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Domain\Teams\Models\Team;
use App\Domain\Teams\Models\TeamInvite;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'invite_token' => ['nullable', 'string'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'status' => UserStatus::Active,
            ]);

            // Handle multi-tenant team & invite onboarding if teams table is present
            if (\Illuminate\Support\Facades\Schema::hasTable('teams')) {
                if (! empty($input['invite_token']) && \Illuminate\Support\Facades\Schema::hasTable('team_invites')) {
                    $invite = TeamInvite::where('token', $input['invite_token'])
                        ->where('status', 'pending')
                        ->where('expires_at', '>', now())
                        ->first();

                    if ($invite) {
                        $invite->accept($user);

                        return $user;
                    }
                }

                $primaryTeam = Team::first();
                if ($primaryTeam) {
                    $primaryTeam->members()->syncWithoutDetaching([
                        $user->id => ['joined_at' => now()],
                    ]);

                    try {
                        $user->assignTeamRole('member', $primaryTeam);
                    } catch (\Throwable) {
                        // Ignore if role not seeded
                    }
                } else {
                    // Initialize default workspace team for initial tenant user
                    $primaryTeam = Team::create([
                        'name' => 'Primary Workspace',
                        'slug' => 'primary-workspace',
                        'owner_id' => $user->id,
                    ]);

                    $primaryTeam->members()->syncWithoutDetaching([
                        $user->id => ['joined_at' => now()],
                    ]);

                    try {
                        $user->assignTeamRole('admin', $primaryTeam);
                    } catch (\Throwable) {
                        // Ignore if role not seeded
                    }
                }
            }

            return $user;
        });
    }
}
