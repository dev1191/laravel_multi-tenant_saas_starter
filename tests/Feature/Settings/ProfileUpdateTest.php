<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $id = 'prof'.strtolower(Str::random(6));
    $this->tenant = Tenant::create([
        'id' => $id,
        'name' => 'Test Tenant',
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

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get("http://{$this->domain}/settings/profile");

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch("http://{$this->domain}/settings/profile", [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect("http://{$this->domain}/settings/profile");

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch("http://{$this->domain}/settings/profile", [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect("http://{$this->domain}/settings/profile");

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete("http://{$this->domain}/settings/profile", [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect("http://{$this->domain}");

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from("http://{$this->domain}/settings/profile")
        ->delete("http://{$this->domain}/settings/profile", [
            'password' => 'wrong-password',
        ], ['HTTP_HOST' => $this->domain]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect("http://{$this->domain}/settings/profile");

    expect($user->fresh())->not->toBeNull();
});
