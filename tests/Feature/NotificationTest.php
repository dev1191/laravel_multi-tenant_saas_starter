<?php

use App\Models\Tenant;
use App\Models\User;
use App\Notifications\GeneralInAppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $id = 'notif'.strtolower(Str::random(6));
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

test('user can receive and fetch database notifications', function () {
    $user = User::factory()->create();

    $user->notify(new GeneralInAppNotification(
        title: 'Task Assigned',
        message: 'You have been assigned to Review PR #42',
        type: 'info',
        actionUrl: '/tasks/42'
    ));

    expect($user->unreadNotifications()->count())->toBe(1);

    $notification = $user->unreadNotifications()->first();
    expect($notification->data['title'])->toBe('Task Assigned');
    expect($notification->data['message'])->toBe('You have been assigned to Review PR #42');
});

test('user can mark notification as read via tenant endpoint', function () {
    $user = User::factory()->create();

    $user->notify(new GeneralInAppNotification(
        title: 'Test Notification',
        message: 'Testing marking as read',
        type: 'success'
    ));

    $notification = $user->unreadNotifications()->first();

    $response = $this
        ->actingAs($user)
        ->post("http://{$this->domain}/notifications/{$notification->id}/read");

    $response->assertSessionHasNoErrors();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
    expect($user->fresh()->readNotifications()->count())->toBe(1);
});

test('user can mark all notifications as read via tenant endpoint', function () {
    $user = User::factory()->create();

    $user->notify(new GeneralInAppNotification('First', 'First message'));
    $user->notify(new GeneralInAppNotification('Second', 'Second message'));

    expect($user->unreadNotifications()->count())->toBe(2);

    $response = $this
        ->actingAs($user)
        ->post("http://{$this->domain}/notifications/read-all");

    $response->assertSessionHasNoErrors();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('notifications page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get("http://{$this->domain}/notifications");

    $response->assertOk();
});
