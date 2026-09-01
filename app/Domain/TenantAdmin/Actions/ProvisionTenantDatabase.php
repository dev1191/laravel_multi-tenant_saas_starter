<?php

namespace App\Domain\TenantAdmin\Actions;

use App\Domain\Settings\Models\TenantLocale;
use App\Domain\Settings\Settings\SiteSettings;
use App\Domain\Teams\Models\Team;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class ProvisionTenantDatabase
{
    use AsAction;

    public function handle(Tenant $tenant, ?string $adminName = null, ?string $adminEmail = null, ?string $adminPassword = null): void
    {
        // 1. Create Tenant Database if it does not already exist
        $manager = $tenant->database()->manager();
        if (! $manager->databaseExists($tenant->database()->getName())) {
            dispatch_sync(new CreateDatabase($tenant));
        }

        // 2. Run Tenant Migrations
        dispatch_sync(new MigrateDatabase($tenant));

        // 3. Initialize Tenancy Context to seed initial data
        tenancy()->initialize($tenant);

        try {
            // Seed Spatie Teams Hierarchical Roles
            $roles = [
                ['name' => 'owner', 'level' => 100, 'guard_name' => 'web'],
                ['name' => 'admin', 'level' => 80, 'guard_name' => 'web'],
                ['name' => 'manager', 'level' => 60, 'guard_name' => 'web'],
                ['name' => 'member', 'level' => 40, 'guard_name' => 'web'],
                ['name' => 'viewer', 'level' => 20, 'guard_name' => 'web'],
            ];

            foreach ($roles as $roleData) {
                Role::firstOrCreate(
                    ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                    ['level' => $roleData['level']]
                );
            }

            // Create initial Workspace Admin / Owner
            $adminEmail = $adminEmail ?? $tenant->email ?? ('admin@'.$tenant->id.'.test');
            $adminName = $adminName ?? $tenant->name ?? 'Workspace Administrator';
            $adminPassword = $adminPassword ?? Str::random(12);

            $admin = User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => Hash::make($adminPassword),
                    'email_verified_at' => now(),
                    'locale' => $tenant->default_locale ?? 'en',
                    'timezone' => $tenant->timezone ?? 'UTC',
                ]
            );

            // Create Primary Workspace Team
            $team = Team::firstOrCreate(
                ['slug' => 'primary'],
                [
                    'name' => $tenant->name.' Team',
                    'owner_id' => $admin->id,
                ]
            );

            // Associate Admin with Team and assign 'owner' role
            $team->members()->syncWithoutDetaching([
                $admin->id => ['joined_at' => now()],
            ]);
            $admin->assignTeamRole('owner', $team);

            // Seed default locales
            TenantLocale::firstOrCreate(
                ['code' => $tenant->default_locale ?? 'en'],
                [
                    'name' => strtoupper($tenant->default_locale ?? 'en'),
                    'direction' => 'ltr',
                    'is_default' => true,
                ]
            );

            // Initialize SiteSettings
            $siteSettings = app(SiteSettings::class);
            $siteSettings->site_name = $tenant->name;
            $siteSettings->primary_color = '#4f46e5';
            $siteSettings->default_locale = $tenant->default_locale ?? 'en';
            $siteSettings->default_currency = $tenant->default_currency ?? 'USD';
            $siteSettings->timezone = $tenant->timezone ?? 'UTC';
            $siteSettings->registration_enabled = true;
            $siteSettings->save();

        } finally {
            tenancy()->end();
        }

        // 4. Update status from 'provisioning' to 'trial' (or 'active')
        $tenant->update([
            'status' => $tenant->plan === 'trial' ? 'trial' : 'active',
            'trial_ends_at' => $tenant->plan === 'trial' ? now()->addDays(14) : null,
        ]);
    }
}
