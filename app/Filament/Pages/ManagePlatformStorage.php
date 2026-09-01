<?php

namespace App\Filament\Pages;

use App\Domain\Settings\Settings\PlatformStorageSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManagePlatformStorage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.platform_storage.title') ?: 'Storage';
    }

    public function getTitle(): string
    {
        return __('messages.platform_storage.page_title') ?: 'Platform Storage Configuration';
    }

    protected static ?int $navigationSort = 7;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(PlatformStorageSettings::class);
        $this->data = $settings->toArray();

        // Mask secret for display
        if (! empty($this->data['secret'])) {
            $this->data['secret'] = '********';
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make()
                ->statePath('data')
                ->schema([
                    Section::make(__('messages.platform_storage.driver_section') ?: 'Storage Driver')
                        ->description(__('messages.platform_storage.driver_description') ?: 'Select the primary filesystem driver.')
                        ->schema([
                            Select::make('driver')
                                ->label(__('messages.platform_storage.driver') ?: 'Storage Driver')
                                ->options(__('messages.platform_storage.drivers') ?: [
                                    'local' => 'Local Disk (storage/app)',
                                    'public' => 'Public Local Disk (storage/app/public)',
                                    's3' => 'Amazon S3 / S3-Compatible Storage',
                                ])
                                ->live()
                                ->required(),
                        ]),

                    Section::make(__('messages.platform_storage.s3_section') ?: 'S3 & Compatible Object Storage')
                        ->description(__('messages.platform_storage.s3_description') ?: 'Configure credentials for Amazon S3 or S3-compatible providers.')
                        ->visible(fn ($get) => $get('driver') === 's3')
                        ->schema([
                            TextInput::make('key')
                                ->label(__('messages.platform_storage.key') ?: 'Access Key ID')
                                ->placeholder('AKIAIOSFODNN7...')
                                ->required(fn ($get) => $get('driver') === 's3'),

                            TextInput::make('secret')
                                ->label(__('messages.platform_storage.secret') ?: 'Secret Access Key')
                                ->password()
                                ->revealable()
                                ->placeholder('••••••••')
                                ->required(fn ($get) => $get('driver') === 's3' && empty(app(PlatformStorageSettings::class)->secret)),

                            TextInput::make('bucket')
                                ->label(__('messages.platform_storage.bucket') ?: 'Bucket Name')
                                ->placeholder('my-tenantforge-bucket')
                                ->required(fn ($get) => $get('driver') === 's3'),

                            TextInput::make('region')
                                ->label(__('messages.platform_storage.region') ?: 'Region')
                                ->placeholder(__('messages.platform_storage.region_placeholder') ?: 'us-east-1, nyc3, auto')
                                ->default('us-east-1')
                                ->required(fn ($get) => $get('driver') === 's3'),

                            TextInput::make('endpoint')
                                ->label(__('messages.platform_storage.endpoint') ?: 'Custom Endpoint URL')
                                ->placeholder('https://nyc3.digitaloceanspaces.com')
                                ->helperText(__('messages.platform_storage.endpoint_helper') ?: 'Leave blank for standard AWS S3.')
                                ->columnSpanFull(),

                            Toggle::make('use_path_style_endpoint')
                                ->label(__('messages.platform_storage.use_path_style_endpoint') ?: 'Use Path Style Endpoint')
                                ->helperText(__('messages.platform_storage.use_path_style_endpoint_helper') ?: 'Enable for MinIO or self-hosted S3 instances.')
                                ->columnSpanFull(),
                        ])->columns(2),
                ]),

            Actions::make([
                Action::make('save')
                    ->label(__('messages.platform_storage.save') ?: 'Save Storage Settings')
                    ->action('save')
                    ->color('primary'),

                Action::make('test')
                    ->label(__('messages.platform_storage.test') ?: 'Test Connection')
                    ->color('gray')
                    ->action('testConnection')
                    ->requiresConfirmation()
                    ->modalHeading(__('messages.platform_storage.test_heading') ?: 'Test Storage Connection')
                    ->modalDescription(__('messages.platform_storage.test_description') ?: 'Attempts a lightweight write and delete probe.')
                    ->modalSubmitActionLabel(__('messages.platform_storage.test_submit') ?: 'Run Test'),
            ]),
        ]);
    }

    public function save(): void
    {
        $settings = app(PlatformStorageSettings::class);

        foreach ($this->data as $key => $value) {
            if (! property_exists($settings, $key)) {
                continue;
            }

            // Don't overwrite secret with the masked placeholder
            if ($key === 'secret' && $value === '********') {
                continue;
            }

            if ($key === 'use_path_style_endpoint') {
                $value = (bool) $value;
            }

            $settings->{$key} = $value;
        }

        $settings->save();

        // Apply immediately to runtime config
        $this->applyToConfig($settings);

        Notification::make()
            ->title(__('messages.platform_storage.saved_success') ?: 'Platform storage settings saved successfully.')
            ->success()
            ->send();
    }

    public function testConnection(): void
    {
        $driver = $this->data['driver'] ?? 'local';
        $settings = app(PlatformStorageSettings::class);

        try {
            if ($driver === 's3') {
                $secret = ($this->data['secret'] ?? null) === '********'
                    ? $settings->secret
                    : ($this->data['secret'] ?? null);

                $config = [
                    'driver' => 's3',
                    'key' => $this->data['key'] ?? $settings->key,
                    'secret' => $secret,
                    'region' => $this->data['region'] ?? $settings->region ?? 'us-east-1',
                    'bucket' => $this->data['bucket'] ?? $settings->bucket,
                    'endpoint' => $this->data['endpoint'] ?? $settings->endpoint,
                    'use_path_style_endpoint' => (bool) ($this->data['use_path_style_endpoint'] ?? $settings->use_path_style_endpoint ?? false),
                    'throw' => true,
                ];

                $disk = Storage::build($config);
            } else {
                $disk = Storage::disk($driver);
            }

            $probeFilename = 'connection-test-'.Str::random(10).'.txt';
            $probeContent = 'TenantForge Storage Connection Probe: '.now()->toIso8601String();

            // 1. Write probe file
            $disk->put($probeFilename, $probeContent);

            // 2. Verify existence
            if (! $disk->exists($probeFilename)) {
                throw new \RuntimeException('Probe file was written but could not be verified on the disk.');
            }

            // 3. Clean up probe file
            $disk->delete($probeFilename);

            Notification::make()
                ->title(__('messages.platform_storage.test_success') ?: 'Storage connection successful!')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title(__('messages.platform_storage.test_failed') ?: 'Storage connection failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function applyToConfig(PlatformStorageSettings $settings): void
    {
        config(['filesystems.default' => $settings->driver]);

        if ($settings->driver === 's3') {
            config([
                'filesystems.disks.s3.key' => $settings->key,
                'filesystems.disks.s3.secret' => $settings->secret,
                'filesystems.disks.s3.region' => $settings->region ?? 'us-east-1',
                'filesystems.disks.s3.bucket' => $settings->bucket,
                'filesystems.disks.s3.endpoint' => $settings->endpoint,
                'filesystems.disks.s3.use_path_style_endpoint' => $settings->use_path_style_endpoint,
            ]);
        }

        app('filesystem')->forgetDisk($settings->driver);
    }
}
