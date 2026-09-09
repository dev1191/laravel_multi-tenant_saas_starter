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

use App\Domain\TenantAdmin\Actions\ProvisionTenantDatabase;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Support\Str;

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
        $isTenantContext = function_exists('tenant') && tenant() !== null;

        $rules = [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'invite_token' => ['nullable', 'string'],
        ];

        if (! $isTenantContext) {
            $rules['workspace_name'] = ['required', 'string', 'max:100'];
            $rules['subdomain'] = [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9-]+$/',
                'unique:tenants,id',
                'not_in:admin,api,app,central,billing,support,mail,root,system,onboarding,dashboard,login,register',
            ];
        }

        Validator::make($input, $rules, [
            'subdomain.regex' => 'The workspace URL may only contain lowercase letters, numbers, and dashes.',
            'subdomain.unique' => 'This workspace URL is already taken.',
            'subdomain.not_in' => 'This workspace URL is reserved.',
        ])->validate();

        return DB::transaction(function () use ($input, $isTenantContext) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'status' => UserStatus::Active,
            ]);

            // If registering on the Central Domain, create and provision the Tenant
            if (! $isTenantContext) {
                $subdomain = Str::lower($input['subdomain']);
                $tenant = Tenant::create([
                    'id' => $subdomain,
                    'name' => $input['workspace_name'],
                    'email' => $input['email'],
                    'plan' => 'trial',
                    'status' => TenantStatus::Provisioning,
                    'data' => [
                        'provisioning_step' => 'initializing',
                        'creator_user_id' => $user->id,
                    ],
                ]);

                $centralDomain = config('tenancy.central_domains.0') ?? request()->getHost();
                $tenant->domains()->create([
                    'domain' => $subdomain.'.'.$centralDomain,
                ]);
                $tenant->domains()->create([
                    'domain' => $subdomain,
                ]);

                // Dispatch asynchronous database provisioning
                ProvisionTenantDatabase::dispatch(
                    $tenant,
                    $user->name,
                    $user->email,
                    $input['password']
                );

                session(['onboarding_tenant_id' => $tenant->id]);

                return $user;
            }

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
