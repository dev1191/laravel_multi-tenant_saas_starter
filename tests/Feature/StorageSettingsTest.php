<?php

use App\Domain\Settings\Settings\PlatformStorageSettings;
use App\Domain\Settings\Settings\SiteSettings;
use App\Domain\TenantAdmin\Models\CentralUser;
use App\Filament\Pages\ManagePlatformStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('platform storage settings have correct defaults and can be persisted', function () {
    $settings = app(PlatformStorageSettings::class);

    expect($settings->driver)->toBeString();
    expect($settings::group())->toBe('platform_storage');

    $settings->driver = 's3';
    $settings->key = 'test-key';
    $settings->secret = 'test-secret';
    $settings->bucket = 'test-bucket';
    $settings->region = 'us-west-2';
    $settings->endpoint = 'https://custom-s3.endpoint.com';
    $settings->use_path_style_endpoint = true;
    $settings->save();

    $reloaded = app(PlatformStorageSettings::class);
    expect($reloaded->driver)->toBe('s3');
    expect($reloaded->key)->toBe('test-key');
    expect($reloaded->secret)->toBe('test-secret');
    expect($reloaded->bucket)->toBe('test-bucket');
    expect($reloaded->region)->toBe('us-west-2');
    expect($reloaded->endpoint)->toBe('https://custom-s3.endpoint.com');
    expect($reloaded->use_path_style_endpoint)->toBeTrue();
});

test('site settings class includes tier 2 storage properties', function () {
    $reflection = new ReflectionClass(SiteSettings::class);

    expect($reflection->hasProperty('storage_driver'))->toBeTrue();
    expect($reflection->hasProperty('storage_key'))->toBeTrue();
    expect($reflection->hasProperty('storage_secret'))->toBeTrue();
    expect($reflection->hasProperty('storage_bucket'))->toBeTrue();
    expect($reflection->hasProperty('storage_region'))->toBeTrue();
    expect($reflection->hasProperty('storage_endpoint'))->toBeTrue();
    expect($reflection->hasProperty('storage_use_path_style_endpoint'))->toBeTrue();
});

test('filament storage page renders and can test local storage connection', function () {
    $admin = CentralUser::create([
        'name' => 'Super Admin',
        'email' => 'admin@tenantforge.test',
        'password' => bcrypt('password'),
        'role' => 'owner',
    ]);

    $this->actingAs($admin);

    Livewire::test(ManagePlatformStorage::class)
        ->assertSuccessful()
        ->set('data.driver', 'local')
        ->call('testConnection')
        ->assertNotified(__('messages.platform_storage.test_success') ?: 'Storage connection successful!');
});

test('filament storage page preserves secret when not changed', function () {
    $settings = app(PlatformStorageSettings::class);
    $settings->driver = 's3';
    $settings->secret = 'super-secret-key-123';
    $settings->key = 'my-access-key';
    $settings->bucket = 'my-bucket';
    $settings->region = 'us-east-1';
    $settings->save();

    $admin = CentralUser::create([
        'name' => 'Super Admin',
        'email' => 'admin2@tenantforge.test',
        'password' => bcrypt('password'),
        'role' => 'owner',
    ]);

    $this->actingAs($admin);

    Livewire::test(ManagePlatformStorage::class)
        ->assertSet('data.secret', '********')
        ->set('data.bucket', 'updated-bucket-name')
        ->call('save')
        ->assertHasNoErrors();

    $reloaded = app(PlatformStorageSettings::class);
    expect($reloaded->bucket)->toBe('updated-bucket-name');
    expect($reloaded->secret)->toBe('super-secret-key-123');
});
