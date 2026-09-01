<?php

namespace Database\Seeders;

use App\Domain\Tasks\Models\Task;
use App\Domain\Teams\Models\Team;
use App\Domain\Teams\Models\TeamInvite;
use App\Domain\TenantAdmin\Actions\ProvisionTenantDatabase;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds for demo tenants.
     */
    public function run(): void
    {
        $tenantsData = [
            [
                'id' => 'acme',
                'name' => 'Acme Corporation',
                'email' => 'admin@acme.com',
                'plan' => 'pro',
                'status' => 'active',
                'default_currency' => 'USD',
                'default_locale' => 'en',
                'timezone' => 'America/New_York',
                'domain' => 'acme.localhost',
                'admin_name' => 'Acme Admin',
                'admin_email' => 'admin@acme.com',
                'admin_password' => 'password',
                'members' => [
                    ['name' => 'Sarah Connor', 'email' => 'sarah@acme.com', 'role' => 'manager'],
                    ['name' => 'Alex Murphy', 'email' => 'alex@acme.com', 'role' => 'member'],
                    ['name' => 'Mike Ross', 'email' => 'mike@acme.com', 'role' => 'viewer'],
                ],
                'tasks' => [
                    ['title' => 'Launch Q3 Product Campaign', 'description' => 'Coordinate with marketing team on digital rollout.', 'status' => 'in_progress', 'priority' => 'high'],
                    ['title' => 'Review Multi-Tenant Security Audit', 'description' => 'Check Spatie permissions and impersonation logs.', 'status' => 'completed', 'priority' => 'high'],
                    ['title' => 'Configure Stripe Webhook Endpoint', 'description' => 'Test customer portal and subscription renewal flow.', 'status' => 'todo', 'priority' => 'medium'],
                    ['title' => 'Customize Site Branding Color', 'description' => 'Update brand primary color in workspace site settings.', 'status' => 'completed', 'priority' => 'low'],
                ],
                'invites' => [
                    ['email' => 'jane.doe@example.com', 'role' => 'member'],
                    ['email' => 'dev.lead@example.com', 'role' => 'manager'],
                ],
            ],
            [
                'id' => 'stark',
                'name' => 'Stark Enterprises',
                'email' => 'tony@stark.com',
                'plan' => 'growth',
                'status' => 'trial',
                'default_currency' => 'EUR',
                'default_locale' => 'en',
                'timezone' => 'Europe/Paris',
                'domain' => 'stark.localhost',
                'admin_name' => 'Tony Stark',
                'admin_email' => 'tony@stark.com',
                'admin_password' => 'password',
                'members' => [
                    ['name' => 'Pepper Potts', 'email' => 'pepper@stark.com', 'role' => 'admin'],
                    ['name' => 'James Rhodes', 'email' => 'rhodey@stark.com', 'role' => 'manager'],
                ],
                'tasks' => [
                    ['title' => 'Upgrade Arc Reactor Grid', 'description' => 'Optimize clean energy distribution across facilities.', 'status' => 'in_progress', 'priority' => 'high'],
                    ['title' => 'Deploy AI Defense Subroutines', 'description' => 'Implement autonomous threat monitoring.', 'status' => 'todo', 'priority' => 'high'],
                ],
                'invites' => [
                    ['email' => 'peter.parker@example.com', 'role' => 'member'],
                ],
            ],
            [
                'id' => 'wayne',
                'name' => 'Wayne Enterprises',
                'email' => 'bruce@wayne.com',
                'plan' => 'starter',
                'status' => 'active',
                'default_currency' => 'GBP',
                'default_locale' => 'en',
                'timezone' => 'Europe/London',
                'domain' => 'wayne.localhost',
                'admin_name' => 'Bruce Wayne',
                'admin_email' => 'bruce@wayne.com',
                'admin_password' => 'password',
                'members' => [
                    ['name' => 'Lucius Fox', 'email' => 'lucius@wayne.com', 'role' => 'admin'],
                    ['name' => 'Alfred Pennyworth', 'email' => 'alfred@wayne.com', 'role' => 'manager'],
                ],
                'tasks' => [
                    ['title' => 'R&D Budget Allocation', 'description' => 'Review annual research and tech infrastructure grants.', 'status' => 'completed', 'priority' => 'medium'],
                    ['title' => 'Secure Satellite Uplink', 'description' => 'Establish encrypted communications channel.', 'status' => 'in_progress', 'priority' => 'high'],
                ],
                'invites' => [],
            ],
        ];

        foreach ($tenantsData as $data) {
            // Drop stale demo database if left over from previous runs
            try {
                DB::statement("DROP DATABASE IF EXISTS `tenant{$data['id']}`");
            } catch (\Throwable) {
                //
            }

            // 1. Create or Find Tenant in Central DB
            $tenant = Tenant::firstOrCreate(
                ['id' => $data['id']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'plan' => $data['plan'],
                    'status' => 'provisioning',
                    'default_currency' => $data['default_currency'],
                    'default_locale' => $data['default_locale'],
                    'timezone' => $data['timezone'],
                ]
            );

            // 2. Attach Central Domains (Supports tenantforge.test, localhost, and raw slug)
            $domains = [
                $data['id'].'.tenantforge.test',
                $data['id'].'.localhost',
                $data['id'],
            ];

            foreach ($domains as $domainName) {
                $tenant->domains()->firstOrCreate(['domain' => $domainName]);
            }

            // 3. Run Provisioning Action (creates DB, runs migrations, seeds roles, admin & settings)
            ProvisionTenantDatabase::run(
                $tenant,
                $data['admin_name'],
                $data['admin_email'],
                $data['admin_password']
            );

            // 4. Populate Workspace Specific Sample Records
            tenancy()->initialize($tenant);

            try {
                $team = Team::first();
                $admin = User::where('email', $data['admin_email'])->first();

                // Add Team Members
                foreach ($data['members'] as $memberData) {
                    $member = User::firstOrCreate(
                        ['email' => $memberData['email']],
                        [
                            'name' => $memberData['name'],
                            'password' => Hash::make('password'),
                            'email_verified_at' => now(),
                        ]
                    );

                    $team->members()->syncWithoutDetaching([
                        $member->id => ['joined_at' => now()],
                    ]);
                    $member->assignTeamRole($memberData['role'], $team);
                }

                // Add Sample Tasks
                foreach ($data['tasks'] as $taskData) {
                    Task::firstOrCreate(
                        ['title' => $taskData['title']],
                        [
                            'description' => $taskData['description'],
                            'status' => $taskData['status'],
                            'priority' => $taskData['priority'],
                            'created_by' => $admin?->id,
                            'assigned_to' => $admin?->id,
                            'team_id' => $team?->id,
                        ]
                    );
                }

                // Add Sample Team Invites
                foreach ($data['invites'] as $inviteData) {
                    TeamInvite::firstOrCreate(
                        ['email' => $inviteData['email'], 'team_id' => $team?->id],
                        [
                            'role' => $inviteData['role'],
                            'token' => Str::random(40),
                            'invited_by' => $admin?->id,
                            'status' => 'pending',
                            'expires_at' => now()->addDays(7),
                        ]
                    );
                }

            } finally {
                tenancy()->end();
            }
        }
    }
}
