<?php

namespace App\Filament\Resources\CentralUserResource\Tables;

use App\Domain\TenantAdmin\Models\CentralUser;
use App\Enums\CentralUserRole;
use App\Filament\Exports\CentralUserExporter;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CentralUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label(__('messages.staff.avatar') ?: 'Avatar')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=FFFFFF&background=4F46E5'),

                TextColumn::make('name')
                    ->label(__('messages.staff.name') ?: 'Full Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('email')
                    ->label(__('messages.staff.email') ?: 'Email Address')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('role')
                    ->label(__('messages.staff.role') ?: 'Role')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label(__('messages.common.created_at') ?: 'Created At')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label(__('messages.staff.role') ?: 'Role')
                    ->options(CentralUserRole::class),

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
            ->filtersFormColumns(3)
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (CentralUser $record): bool => $record->id === auth('central')->id()),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(CentralUserExporter::class)
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label(__('messages.staff.export_staff') ?: 'Export Staff Accounts'),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->exporter(CentralUserExporter::class),
            ]);
    }
}
