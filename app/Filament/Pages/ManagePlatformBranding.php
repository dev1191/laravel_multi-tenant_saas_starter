<?php

namespace App\Filament\Pages;

use App\Domain\Settings\Settings\PlatformBrandingSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManagePlatformBranding extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.platform_branding.title') ?: 'Platform Branding';
    }

    public function getTitle(): string
    {
        return __('messages.platform_branding.page_title') ?: 'Platform Branding & Logos';
    }

    protected static ?int $navigationSort = 4;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(PlatformBrandingSettings::class);
        $this->data = $settings->toArray();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make()
                ->statePath('data')
                ->schema([
                    Section::make('General Branding')
                        ->description('Configure platform name and visual identification.')
                        ->schema([
                            TextInput::make('brand_name')
                                ->label('Platform Name')
                                ->required()
                                ->placeholder('TenantForge Central'),

                            ColorPicker::make('primary_color')
                                ->label('Primary Accent Color')
                                ->placeholder('#4f46e5'),
                        ])->columns(2),

                    Section::make('Logos & Assets')
                        ->description('Upload or manage Light and Dark mode logos displayed on navigation headers, emails, and landing pages.')
                        ->schema([
                            FileUpload::make('logo_light_path')
                                ->label('Light Mode Logo')
                                ->disk('public')
                                ->directory('branding')
                                ->visibility('public')
                                ->image()
                                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                                ->maxSize(5120)
                                ->helperText('Used against light backgrounds (e.g. navigation headers in light mode). Accepts PNG, SVG, WEBP up to 5MB.'),

                            FileUpload::make('logo_dark_path')
                                ->label('Dark Mode Logo')
                                ->disk('public')
                                ->directory('branding')
                                ->visibility('public')
                                ->image()
                                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                                ->maxSize(5120)
                                ->helperText('Used against dark backgrounds (e.g. navigation headers in dark mode). Accepts PNG, SVG, WEBP up to 5MB.'),

                            FileUpload::make('favicon_path')
                                ->label('Browser Favicon')
                                ->disk('public')
                                ->directory('branding')
                                ->visibility('public')
                                ->acceptedFileTypes(['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/svg+xml'])
                                ->maxSize(2048)
                                ->helperText('Favicon icon displayed in browser tabs (.ico, .png, .svg).')
                                ->columnSpanFull(),
                        ])->columns(2),
                ]),

            Actions::make([
                Action::make('save')
                    ->label('Save Platform Branding')
                    ->action('save')
                    ->color('primary'),
            ]),
        ]);
    }

    public function save(): void
    {
        $settings = app(PlatformBrandingSettings::class);

        foreach ($this->data as $key => $value) {
            if (property_exists($settings, $key)) {
                $settings->{$key} = $value;
            }
        }

        $settings->save();

        Notification::make()
            ->title('Platform branding settings saved successfully.')
            ->success()
            ->send();
    }
}
