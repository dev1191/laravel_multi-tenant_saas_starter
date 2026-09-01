<?php

namespace App\Filament\Pages;

use App\Domain\Settings\Settings\PlatformMailSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;

class ManagePlatformMail extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.platform_mail.title');
    }

    public function getTitle(): string
    {
        return __('messages.platform_mail.page_title');
    }

    protected static ?int $navigationSort = 6;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(PlatformMailSettings::class);
        $this->data = $settings->toArray();

        // Mask password for display
        if (! empty($this->data['password'])) {
            $this->data['password'] = '********';
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make()
                ->statePath('data')
                ->schema([
                    Section::make(__('messages.platform_mail.transport_section'))
                        ->description(__('messages.platform_mail.transport_description'))
                        ->schema([
                            Select::make('mailer')
                                ->label(__('messages.platform_mail.mailer'))
                                ->options(__('messages.platform_mail.drivers'))
                                ->required(),

                            TextInput::make('host')
                                ->label(__('messages.platform_mail.host'))
                                ->placeholder('smtp.postmarkapp.com, email-smtp.us-east-1.amazonaws.com, etc.'),

                            TextInput::make('port')
                                ->label(__('messages.platform_mail.port'))
                                ->numeric()
                                ->placeholder('587'),

                            TextInput::make('username')
                                ->label(__('messages.platform_mail.username'))
                                ->placeholder('apikey, AKIAIOSFODNN7...'),

                            TextInput::make('password')
                                ->label(__('messages.platform_mail.password'))
                                ->password()
                                ->revealable()
                                ->placeholder('••••••••'),

                            Select::make('encryption')
                                ->label(__('messages.platform_mail.encryption'))
                                ->options(__('messages.platform_mail.encryption_options')),
                        ])->columns(2),

                    Section::make(__('messages.platform_mail.sender_section'))
                        ->description(__('messages.platform_mail.sender_description'))
                        ->schema([
                            TextInput::make('from_address')
                                ->label(__('messages.platform_mail.from_address'))
                                ->email()
                                ->placeholder('noreply@tenantforge.com'),

                            TextInput::make('from_name')
                                ->label(__('messages.platform_mail.from_name'))
                                ->placeholder('TenantForge'),
                        ])->columns(2),
                ]),

            Actions::make([
                Action::make('save')
                    ->label(__('messages.platform_mail.save'))
                    ->action('save')
                    ->color('primary'),

                Action::make('test')
                    ->label(__('messages.platform_mail.test'))
                    ->color('gray')
                    ->action('sendTestEmail')
                    ->requiresConfirmation()
                    ->modalHeading(__('messages.platform_mail.test_heading'))
                    ->modalDescription(__('messages.platform_mail.test_description'))
                    ->modalSubmitActionLabel(__('messages.platform_mail.test_submit')),
            ]),
        ]);
    }

    public function save(): void
    {
        $settings = app(PlatformMailSettings::class);

        foreach ($this->data as $key => $value) {
            if (! property_exists($settings, $key)) {
                continue;
            }

            // Don't overwrite password with the masked placeholder
            if ($key === 'password' && $value === '********') {
                continue;
            }

            if ($key === 'port') {
                $value = $value ? (int) $value : null;
            }

            $settings->{$key} = $value;
        }

        $settings->save();

        // Apply immediately to running config
        $this->applyToConfig($settings);

        Notification::make()
            ->title(__('messages.platform_mail.saved_success'))
            ->success()
            ->send();
    }

    public function sendTestEmail(): void
    {
        $settings = app(PlatformMailSettings::class);
        $this->applyToConfig($settings);

        $recipient = auth()->user()->email;

        try {
            Mail::raw("This is a test email from TenantForge platform mail configuration. If you're reading this, your mail settings are working correctly!", function ($message) use ($recipient) {
                $message->to($recipient)->subject('TenantForge Platform Mail Test');
            });

            Notification::make()
                ->title(__('messages.platform_mail.test_sent', ['email' => $recipient]))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title(__('messages.platform_mail.test_failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function applyToConfig(PlatformMailSettings $settings): void
    {
        if ($settings->mailer === 'smtp') {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $settings->host,
                'mail.mailers.smtp.port' => $settings->port ?? 587,
                'mail.mailers.smtp.username' => $settings->username,
                'mail.mailers.smtp.password' => $settings->password,
                'mail.mailers.smtp.encryption' => $settings->encryption === 'none' ? null : $settings->encryption,
            ]);
        } else {
            config(['mail.default' => $settings->mailer]);
        }

        if ($settings->from_address) {
            config(['mail.from.address' => $settings->from_address]);
        }
        if ($settings->from_name) {
            config(['mail.from.name' => $settings->from_name]);
        }

        app('mail.manager')->forgetMailers();
    }
}
