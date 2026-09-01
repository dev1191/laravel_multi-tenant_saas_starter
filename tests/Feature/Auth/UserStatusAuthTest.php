<?php

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('active users can authenticate', function () {
    $user = User::factory()->create([
        'status' => UserStatus::Active,
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('suspended users cannot authenticate', function () {
    $user = User::factory()->create([
        'status' => UserStatus::Suspended,
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('inactive users cannot authenticate', function () {
    $user = User::factory()->create([
        'status' => UserStatus::Inactive,
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});
