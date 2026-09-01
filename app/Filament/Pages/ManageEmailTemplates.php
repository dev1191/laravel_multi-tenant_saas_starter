<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

class ManageEmailTemplates extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.email_templates.title');
    }

    public function getTitle(): string
    {
        return __('messages.email_templates.page_title');
    }

    protected static ?int $navigationSort = 7;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [
        'template' => 'tenant_provisioned',
        'brand_name' => 'TenantForge',
        'primary_color' => '#4f46e5',
        'device' => 'desktop',
    ];

    public function mount(): void
    {
        $this->data['brand_name'] = config('app.name', 'TenantForge');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make()
                ->statePath('data')
                ->live()
                ->schema([
                    Grid::make(12)->schema([
                        Section::make(__('messages.email_templates.template_details'))
                            ->columnSpan(4)
                            ->schema([
                                Select::make('template')
                                    ->label(__('messages.email_templates.select_template'))
                                    ->options(__('messages.email_templates.templates'))
                                    ->required()
                                    ->live(),

                                TextInput::make('brand_name')
                                    ->label('Brand / Platform Name')
                                    ->default(config('app.name', 'TenantForge'))
                                    ->live(debounce: 300),

                                TextInput::make('primary_color')
                                    ->label('Brand Primary Color')
                                    ->default('#4f46e5')
                                    ->live(debounce: 300),

                                ToggleButtons::make('device')
                                    ->label('Preview Device Mode')
                                    ->options([
                                        'desktop' => 'Desktop (580px)',
                                        'mobile' => 'Mobile (375px)',
                                    ])
                                    ->icons([
                                        'desktop' => 'heroicon-o-computer-desktop',
                                        'mobile' => 'heroicon-o-device-phone-mobile',
                                    ])
                                    ->inline()
                                    ->default('desktop')
                                    ->live(),
                            ]),

                        Section::make(__('messages.email_templates.live_preview'))
                            ->description(__('messages.email_templates.preview_help'))
                            ->columnSpan(8)
                            ->schema([
                                Html::make(fn () => $this->renderPreviewIframe()),
                            ]),
                    ]),
                ]),

            Actions::make([
                Action::make('sendTest')
                    ->label(__('messages.email_templates.send_test'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->action('sendSampleEmail'),
            ]),
        ]);
    }

    public function renderPreviewIframe(): HtmlString
    {
        $template = $this->data['template'] ?? 'tenant_provisioned';
        $brandName = $this->data['brand_name'] ?? config('app.name', 'TenantForge');
        $primaryColor = $this->data['primary_color'] ?? '#4f46e5';
        $device = $this->data['device'] ?? 'desktop';

        $width = $device === 'mobile' ? '375px' : '100%';
        $maxWidth = $device === 'mobile' ? '375px' : '650px';

        $html = $this->renderTemplateHtml($template, $brandName, $primaryColor);
        $escapedHtml = htmlspecialchars($html, ENT_QUOTES, 'UTF-8');

        return new HtmlString("
            <div style='display: flex; justify-content: center; background-color: #f1f5f9; padding: 24px; border-radius: 8px; border: 1px dashed #cbd5e1;'>
                <iframe 
                    srcdoc=\"{$escapedHtml}\"
                    style=\"width: {$width}; max-width: {$maxWidth}; height: 560px; border: none; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); background: #ffffff; transition: width 0.3s ease;\"
                ></iframe>
            </div>
        ");
    }

    protected function renderTemplateHtml(string $template, string $brandName, string $primaryColor): string
    {
        $sampleData = match ($template) {
            'tenant_provisioned' => [
                'tenantName' => 'Acme Corporation',
                'domainUrl' => 'https://acme.tenantforge.test',
                'planName' => 'Scale Annual',
                'primaryColor' => $primaryColor,
                'brandName' => $brandName,
            ],
            'invoice_receipt' => [
                'tenantName' => 'Acme Corporation',
                'invoiceNumber' => 'INV-2026-0089',
                'amountPaid' => '$79.00 USD',
                'planName' => 'Enterprise Monthly',
                'billingPeriod' => now()->format('M d, Y') . ' - ' . now()->addMonth()->format('M d, Y'),
                'invoiceUrl' => '#',
                'primaryColor' => $primaryColor,
                'brandName' => $brandName,
            ],
            'admin_reset' => [
                'userName' => auth()->user()?->name ?? 'Admin User',
                'resetUrl' => '#',
                'primaryColor' => $primaryColor,
                'brandName' => $brandName,
            ],
            'team_invitation' => [
                'teamName' => $brandName . ' Team',
                'inviterName' => 'Sarah Connor',
                'role' => 'Workspace Admin',
                'inviteUrl' => '#',
                'expiresAt' => now()->addDays(7)->toFormattedDateString(),
                'primaryColor' => $primaryColor,
                'brandName' => $brandName,
            ],
            default => [
                'tenantName' => 'Sample Workspace',
                'domainUrl' => 'https://sample.tenantforge.test',
                'planName' => 'Pro Plan',
                'primaryColor' => $primaryColor,
                'brandName' => $brandName,
            ],
        };

        try {
            return view("emails.{$template}", $sampleData)->render();
        } catch (\Throwable $e) {
            return "<div style='padding: 20px; color: red;'><strong>Template render error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }

    public function sendSampleEmail(): void
    {
        $template = $this->data['template'] ?? 'tenant_provisioned';
        $brandName = $this->data['brand_name'] ?? config('app.name', 'TenantForge');
        $primaryColor = $this->data['primary_color'] ?? '#4f46e5';

        $recipient = auth()->user()?->email;
        if (! $recipient) {
            Notification::make()->title('No authenticated user email found')->danger()->send();
            return;
        }

        $html = $this->renderTemplateHtml($template, $brandName, $primaryColor);
        $subject = "Sample Email: " . (__("messages.email_templates.templates.{$template}") ?? ucfirst($template));

        try {
            Mail::html($html, function ($message) use ($recipient, $subject) {
                $message->to($recipient)->subject($subject);
            });

            Notification::make()
                ->title(__('messages.email_templates.test_sent', [
                    'template' => __("messages.email_templates.templates.{$template}"),
                    'email' => $recipient,
                ]))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Failed to send sample email')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
