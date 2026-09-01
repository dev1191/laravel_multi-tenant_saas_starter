<?php

namespace App\Filament\Resources\TenantResource\Tables;

use App\Domain\TenantAdmin\Models\ImpersonationLog;
use App\Enums\TenantStatus;
use App\Filament\Exports\TenantExporter;
use App\Models\Tenant;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_light_path')
                    ->label('Logo')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=FFFFFF&background=4F46E5')
                    ->toggleable(),
                TextColumn::make('id')
                    ->label(__('messages.tenant.subdomain'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label(__('messages.tenant.workspace'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('messages.auth.email'))
                    ->searchable(),
                TextColumn::make('primary_domain')
                    ->label(__('messages.tenant.domain'))
                    ->getStateUsing(fn (Tenant $record): ?string => $record->primary_domain)
                    ->copyable()
                    ->copyMessage(__('messages.tenant.domain_copied'))
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('domains', fn ($q) => $q->where('domain', 'like', "%{$search}%"))
                            ->orWhere('id', 'like', "%{$search}%");
                    }),
                TextColumn::make('status')
                    ->label(__('messages.common.status'))
                    ->badge(),
                TextColumn::make('plan')
                    ->label(__('messages.billing.current_plan'))
                    ->badge(),
                TextColumn::make('trial_ends_at')
                    ->label(__('messages.tenant.trial_expiry'))
                    ->dateTime('M j, Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.common.created_at'))
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('messages.common.status'))
                    ->options(TenantStatus::class),
                SelectFilter::make('plan')
                    ->label(__('messages.billing.current_plan'))
                    ->options([
                        'trial' => 'Trial',
                        'starter' => 'Starter',
                        'pro' => 'Pro',
                        'business' => 'Business',
                    ]),
                Filter::make('created_at')
                    ->label(__('messages.common.created_at'))
                    ->columnSpan(['default' => 1, 'sm' => 2, 'lg' => 2])
                    ->columns(2)
                    ->form([
                        Flatpickr::make('created_from')
                            ->label(__('messages.common.created_from'))
                            ->dateFormat('Y-m-d')
                            ->displayFormat('M j, Y')
                            ->prefixIcon('heroicon-m-calendar-days'),
                        Flatpickr::make('created_until')
                            ->label(__('messages.common.created_until'))
                            ->dateFormat('Y-m-d')
                            ->displayFormat('M j, Y')
                            ->prefixIcon('heroicon-m-calendar-days'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Created from '.\Carbon\Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until '.\Carbon\Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Action::make('impersonate')
                    ->label(__('messages.tenant.impersonate'))
                    ->icon('heroicon-o-arrow-right-end-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('messages.tenant.impersonate_heading'))
                    ->modalDescription(__('messages.tenant.impersonate_description'))
                    ->action(function (Tenant $record) {
                        $token = Str::random(40);

                        ImpersonationLog::create([
                            'token' => $token,
                            'central_user_id' => auth('central')->id(),
                            'tenant_id' => $record->id,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'started_at' => now(),
                        ]);

                        $host = request()->getHost();
                        if ($host === '127.0.0.1') {
                            $host = 'localhost';
                        }

                        $domain = $record->id.'.'.$host;
                        $scheme = request()->getScheme();
                        $port = request()->getPort();
                        $portSuffix = ($port && ! in_array((int) $port, [80, 443])) ? ':'.$port : '';

                        $targetUrl = "{$scheme}://{$domain}{$portSuffix}/impersonate/{$token}";

                        return redirect()->away($targetUrl);
                    }),

                ActionGroup::make([
                    Action::make('extend_trial')
                        ->label(__('messages.tenant.extend_trial'))
                        ->icon('heroicon-o-clock')
                        ->color('info')
                        ->form([
                            Select::make('days')
                                ->label(__('messages.tenant.extend_by_days'))
                                ->options([
                                    '7' => '7 '.__('messages.common.days'),
                                    '14' => '14 '.__('messages.common.days'),
                                    '30' => '30 '.__('messages.common.days'),
                                    '60' => '60 '.__('messages.common.days'),
                                ])
                                ->default('14')
                                ->required(),
                        ])
                        ->action(function (Tenant $record, array $data) {
                            $currentEnd = ($record->trial_ends_at && $record->trial_ends_at->isFuture())
                                ? $record->trial_ends_at
                                : now();

                            $newEnd = $currentEnd->addDays((int) $data['days']);

                            $record->update([
                                'status' => 'trial',
                                'trial_ends_at' => $newEnd,
                            ]);

                            Notification::make()
                                ->title(__('messages.tenant.extend_trial'))
                                ->body("Trial for {$record->name} extended to ".$newEnd->toFormattedDateString())
                                ->success()
                                ->send();
                        }),

                    Action::make('toggle_status')
                        ->label(fn (Tenant $record) => $record->isSuspended() ? __('messages.tenant.activate') : __('messages.tenant.suspend'))
                        ->icon(fn (Tenant $record) => $record->isSuspended() ? 'heroicon-o-check-circle' : 'heroicon-o-no-symbol')
                        ->color(fn (Tenant $record) => $record->isSuspended() ? 'success' : 'danger')
                        ->requiresConfirmation()
                        ->action(function (Tenant $record) {
                            $newStatus = $record->isSuspended() ? TenantStatus::Active : TenantStatus::Suspended;
                            $record->update(['status' => $newStatus]);

                            Notification::make()
                                ->title("Tenant status changed to {$newStatus->value}")
                                ->success()
                                ->send();
                        }),

                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(TenantExporter::class)
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label(__('messages.tenant.export_workspaces')),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->exporter(TenantExporter::class),
            ]);
    }
}
