<?php

namespace App\Filament\Exports;

use App\Domain\TenantAdmin\Models\CentralUser;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CentralUserExporter extends Exporter
{
    protected static ?string $model = CentralUser::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name')
                ->label(__('messages.staff.name') ?: 'Full Name'),
            ExportColumn::make('email')
                ->label(__('messages.staff.email') ?: 'Email Address'),
            ExportColumn::make('role')
                ->label(__('messages.staff.role') ?: 'Role')
                ->state(fn (CentralUser $record): string => $record->role instanceof \App\Enums\CentralUserRole ? $record->role->getLabel() : (string) $record->role),
            ExportColumn::make('created_at')
                ->label(__('messages.common.created_at') ?: 'Created At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('messages.staff.export_completed', [
            'count' => number_format($export->successful_rows),
            'rows' => str('row')->plural($export->successful_rows),
        ]) ?: ('Your staff accounts export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.');

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $failedMsg = __('messages.staff.export_failed_rows', [
                'count' => number_format($failedRowsCount),
                'rows' => str('row')->plural($failedRowsCount),
            ]) ?: (number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.');

            $body .= ' '.$failedMsg;
        }

        return $body;
    }
}
