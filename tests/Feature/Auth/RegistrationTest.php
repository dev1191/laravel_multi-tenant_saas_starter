<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    \Illuminate\Support\Facades\Queue::fake();

    $slug = 'test'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6));

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => "test_{$slug}@example.com",
        'password' => 'password',
        'password_confirmation' => 'password',
        'workspace_name' => 'Test Workspace',
        'subdomain' => $slug,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect("/onboarding/{$slug}");
});
