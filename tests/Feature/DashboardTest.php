<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $id = 'dash'.strtolower(Str::random(6));
    $this->tenant = Tenant::create([
        'id' => $id,
        'name' => 'Dashboard Test Workspace',
        'status' => 'active',
    ]);
    $this->domain = $id.'.'.(config('tenancy.central_domains.0') ?? 'tenantforge.test');
    $this->tenant->domains()->create(['domain' => $id]);
    $this->tenant->domains()->create(['domain' => $this->domain]);
    tenancy()->initialize($this->tenant);
});

afterEach(function () {
    if (tenancy()->initialized) {
        tenancy()->end();
    }
    if (isset($this->tenant)) {
        $this->tenant->delete();
    }
});

test('guests are redirected to the login page on tenant domain', function () {
    $response = $this->get("http://{$this->domain}/dashboard");
    $response->assertRedirect("http://{$this->domain}/login");
});

test('authenticated users can visit the dashboard on tenant domain', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get("http://{$this->domain}/dashboard");

    $response->assertOk();
});
