<?php

use App\Domain\TenantAdmin\Actions\ProvisionTenantDatabase;
use App\Domain\TenantAdmin\Actions\RetryTenantProvisioning;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Decorators\JobDecorator;

uses(RefreshDatabase::class);

test('central registration creates user, tenant in provisioning state, and redirects to onboarding', function () {
    Queue::fake();

    $slug = 'tenant'.Str::lower(Str::random(6));

    $response = $this->post('/register', [
        'name' => 'Alice Founder',
        'email' => "alice_{$slug}@example.com",
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'workspace_name' => 'Acme Dynamics',
        'subdomain' => $slug,
    ]);

    $response->assertRedirect("/onboarding/{$slug}");

    $this->assertDatabaseHas('users', [
        'email' => "alice_{$slug}@example.com",
    ]);

    $tenant = Tenant::find($slug);
    expect($tenant)->not->toBeNull();
    expect($tenant->name)->toBe('Acme Dynamics');
    expect($tenant->isProvisioning())->toBeTrue();
    expect($tenant->getProvisioningStep())->toBe('initializing');

    Queue::assertPushed(function (JobDecorator $job) {
        return $job->getAction() instanceof ProvisionTenantDatabase;
    });
});

test('tenant model tracks provisioning steps and progress percentages accurately', function () {
    $slug = 'tenant'.Str::lower(Str::random(6));

    $tenant = Tenant::create([
        'id' => $slug,
        'name' => 'Beta Workspace',
        'email' => "beta_{$slug}@test.com",
        'status' => TenantStatus::Provisioning,
    ]);

    expect($tenant->getProvisioningStep())->toBe('initializing');
    expect($tenant->getProvisioningPercent())->toBe(10);

    $tenant->setProvisioningStep('creating_database');
    expect($tenant->getProvisioningStep())->toBe('creating_database');
    expect($tenant->getProvisioningPercent())->toBe(35);

    $tenant->setProvisioningStep('migrating_database');
    expect($tenant->getProvisioningStep())->toBe('migrating_database');
    expect($tenant->getProvisioningPercent())->toBe(65);

    $tenant->setProvisioningStep('seeding_defaults');
    expect($tenant->getProvisioningStep())->toBe('seeding_defaults');
    expect($tenant->getProvisioningPercent())->toBe(90);

    $tenant->setProvisioningStep('completed');
    expect($tenant->getProvisioningStep())->toBe('completed');
    expect($tenant->getProvisioningPercent())->toBe(100);

    $tenant->setProvisioningStep('failed', 'Database connection refused');
    expect($tenant->isFailed())->toBeFalse();
    $tenant->update(['status' => TenantStatus::Failed]);
    expect($tenant->isFailed())->toBeTrue();
    expect($tenant->getProvisioningError())->toBe('Database connection refused');
});

test('onboarding status endpoint returns json with step, progress and target dashboard url', function () {
    $slug = 'tenant'.Str::lower(Str::random(6));

    $tenant = Tenant::create([
        'id' => $slug,
        'name' => 'Gamma Workspace',
        'email' => "gamma_{$slug}@test.com",
        'status' => TenantStatus::Provisioning,
    ]);
    $tenant->setProvisioningStep('migrating_database');

    $response = $this->getJson("/onboarding/{$tenant->id}/status");

    $response->assertOk();
    $response->assertJson([
        'tenant_id' => $slug,
        'name' => 'Gamma Workspace',
        'status' => 'provisioning',
        'step' => 'migrating_database',
        'progress_percent' => 65,
        'is_ready' => false,
    ]);
});

test('retry tenant provisioning resets step to initializing and dispatches job', function () {
    Queue::fake();

    $slug = 'tenant'.Str::lower(Str::random(6));

    $tenant = Tenant::create([
        'id' => $slug,
        'name' => 'Delta Workspace',
        'email' => "delta_{$slug}@test.com",
        'status' => TenantStatus::Failed,
        'data' => [
            'provisioning_step' => 'failed',
            'provisioning_error' => 'Timeout occurred',
        ],
    ]);

    expect($tenant->isFailed())->toBeTrue();

    RetryTenantProvisioning::run($tenant);

    $tenant->refresh();
    expect($tenant->isProvisioning())->toBeTrue();
    expect($tenant->getProvisioningStep())->toBe('initializing');
    expect($tenant->getProvisioningError())->toBeNull();

    Queue::assertPushed(function (JobDecorator $job) {
        return $job->getAction() instanceof ProvisionTenantDatabase;
    });
});
