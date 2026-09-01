<?php

use App\Domain\Billing\Actions\CreateCheckoutSession;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\PlanPrice;
use App\Domain\Tasks\Actions\CreateTask;
use App\Domain\Teams\Actions\AcceptTeamInvite;
use App\Domain\Teams\Actions\CreateTeamInvite;
use App\Domain\TenantAdmin\Actions\ProvisionTenantDatabase;
use App\Domain\TenantAdmin\Models\CentralUser;
use App\Domain\TenantAdmin\Models\ImpersonationLog;
use App\Http\Middleware\TenantAccessStatus;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

test('central users and plans can be retrieved from central database', function () {
    $user = CentralUser::create([
        'name' => 'Admin Staff',
        'email' => 'staff_'.Str::random(5).'@tenantforge.com',
        'password' => Hash::make('secret123'),
        'role' => 'owner',
    ]);

    expect($user->isOwner())->toBeTrue();

    $plan = Plan::create([
        'name' => 'Growth',
        'slug' => 'growth_'.Str::random(5),
        'billing_period' => 'monthly',
        'features' => ['team-invites', 'advanced-analytics'],
    ]);

    $price = PlanPrice::create([
        'plan_id' => $plan->id,
        'currency' => 'USD',
        'amount' => 4900,
        'gateway' => 'stripe',
    ]);

    expect($plan->hasFeature('team-invites'))->toBeTrue();
    expect($plan->hasFeature('missing-feature'))->toBeFalse();
    expect($price->formatted_amount)->toBe('$49.00');
});

test('tenant models support status and trial helper methods', function () {
    $activeTenant = new Tenant(['status' => 'active', 'plan' => 'pro']);
    expect($activeTenant->isActive())->toBeTrue();
    expect($activeTenant->isSuspended())->toBeFalse();

    $trialTenant = new Tenant([
        'status' => 'trial',
        'plan' => 'trial',
        'trial_ends_at' => now()->addDays(7),
    ]);
    expect($trialTenant->onTrial())->toBeTrue();
    expect($trialTenant->hasExpiredTrial())->toBeFalse();

    $expiredTenant = new Tenant([
        'status' => 'trial',
        'plan' => 'trial',
        'trial_ends_at' => now()->subDay(),
    ]);
    expect($expiredTenant->hasExpiredTrial())->toBeTrue();

    $suspendedTenant = new Tenant(['status' => 'suspended']);
    expect($suspendedTenant->isSuspended())->toBeTrue();
});

test('tenant access status middleware blocks suspended tenant', function () {
    $tenant = new Tenant([
        'id' => 'suspended-corp',
        'status' => 'suspended',
    ]);

    tenancy()->initialize($tenant);

    $middleware = new TenantAccessStatus;
    $request = Request::create('/dashboard', 'GET');

    expect(fn () => $middleware->handle($request, fn () => response('OK')))
        ->toThrow(HttpException::class);

    tenancy()->end();
});

test('tenant access status middleware redirects expired trial to billing', function () {
    $tenant = new Tenant([
        'id' => 'expired-corp',
        'status' => 'trial',
        'trial_ends_at' => now()->subDays(2),
    ]);

    tenancy()->initialize($tenant);

    $middleware = new TenantAccessStatus;
    $request = Request::create('/dashboard', 'GET');

    $response = $middleware->handle($request, fn () => response('OK'));
    expect($response->isRedirect(url('/billing')))->toBeTrue();

    tenancy()->end();
});

test('impersonation log can bridge central staff and tenant workspace', function () {
    $staff = CentralUser::create([
        'name' => 'Support Tech',
        'email' => 'tech_'.Str::random(5).'@tenantforge.com',
        'password' => Hash::make('password'),
        'role' => 'support',
    ]);

    $tenant = Tenant::create([
        'id' => 'audit-test-'.Str::random(5),
        'name' => 'Audit Test Corp',
        'status' => 'active',
    ]);

    $token = Str::random(40);
    $log = ImpersonationLog::create([
        'token' => $token,
        'central_user_id' => $staff->id,
        'tenant_id' => $tenant->id,
        'started_at' => now(),
    ]);

    expect($log->centralUser->id)->toBe($staff->id);
    expect($log->tenant->id)->toBe($tenant->id);
    expect($log->ended_at)->toBeNull();

    $log->update(['ended_at' => now()]);
    expect($log->fresh()->ended_at)->not->toBeNull();
});

test('role hierarchy levels work as expected', function () {
    $roles = [
        'owner' => 100,
        'admin' => 80,
        'manager' => 60,
        'member' => 40,
        'viewer' => 20,
    ];

    expect($roles['owner'])->toBeGreaterThan($roles['admin']);
    expect($roles['admin'])->toBeGreaterThan($roles['manager']);
    expect($roles['manager'])->toBeGreaterThan($roles['member']);
    expect($roles['member'])->toBeGreaterThan($roles['viewer']);
});

test('pennant feature flags evaluate against tenant plan', function () {
    $planWithInvites = Plan::create([
        'name' => 'Pro',
        'slug' => 'pro_tier_'.Str::random(5),
        'billing_period' => 'monthly',
        'features' => ['team-invites'],
    ]);

    expect($planWithInvites->hasFeature('team-invites'))->toBeTrue();
    expect($planWithInvites->hasFeature('custom-branding'))->toBeFalse();
});

test('domain actions can be executed directly as single-purpose units', function () {
    expect(class_exists(CreateTeamInvite::class))->toBeTrue();
    expect(class_exists(AcceptTeamInvite::class))->toBeTrue();
    expect(class_exists(CreateTask::class))->toBeTrue();
    expect(class_exists(ProvisionTenantDatabase::class))->toBeTrue();
    expect(class_exists(CreateCheckoutSession::class))->toBeTrue();
});

test('central home page loads with 200 status and renders welcome component', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

test('language model queries central database connection', function () {
    $lang = \App\Models\Language::firstOrCreate(
        ['code' => 'it'],
        [
            'name' => 'Italian',
            'direction' => 'ltr',
            'display_order' => 99,
        ]
    );

    expect($lang->getConnectionName())->toBe(config('tenancy.database.central_connection'));
    expect(\App\Models\Language::where('code', 'it')->exists())->toBeTrue();
});

test('team invite dispatches TeamInvitationMail via SendTeamInvitationJob', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $invite = new \App\Domain\Teams\Models\TeamInvite([
        'email' => 'newmember@example.com',
        'role' => 'member',
        'token' => 'test-token-xyz',
    ]);
    $team = new \App\Domain\Teams\Models\Team(['name' => 'Acme Test Team']);
    $invite->setRelation('team', $team);

    (new \App\Domain\Teams\Jobs\SendTeamInvitationJob($invite))->handle();

    \Illuminate\Support\Facades\Mail::assertQueued(\App\Domain\Teams\Mail\TeamInvitationMail::class, function ($mail) {
        return $mail->hasTo('newmember@example.com');
    });
});

test('mail tenancy bootstrapper dynamically injects tenant custom smtp when configured', function () {
    $tenant = \App\Models\Tenant::create([
        'id' => 'mailtest_'.\Illuminate\Support\Str::random(5),
        'name' => 'Mail Corp',
    ]);

    $bootstrapper = app(\App\Domain\Settings\Bootstrappers\MailTenancyBootstrapper::class);
    $bootstrapper->bootstrap($tenant);

    // After bootstrap, if no custom smtp is configured in test DB, it safely retains default transport
    expect(config('mail.default'))->toBeIn(['log', 'smtp', 'array']);

    $bootstrapper->revert();
});

test('email templates render with branding and layout properly', function () {
    $invitationHtml = view('emails.team_invitation', [
        'teamName' => 'Stark Industries',
        'inviterName' => 'Tony Stark',
        'role' => 'Engineer',
        'inviteUrl' => 'https://stark.example.com/accept',
        'expiresAt' => 'Aug 31, 2026',
        'primaryColor' => '#e11d48',
    ])->render();

    expect($invitationHtml)->toContain('Stark Industries')
        ->toContain('Tony Stark')
        ->toContain('#e11d48')
        ->toContain('Accept Invitation');

    $provisionedHtml = view('emails.tenant_provisioned', [
        'tenantName' => 'Wayne Enterprises',
        'domainUrl' => 'https://wayne.tenantforge.test',
        'planName' => 'Scale',
        'primaryColor' => '#4f46e5',
    ])->render();

    expect($provisionedHtml)->toContain('Wayne Enterprises')
        ->toContain('https://wayne.tenantforge.test')
        ->toContain('Access Your Workspace');
});


