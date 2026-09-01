<?php

namespace App\Filament\Exports;

use App\Models\Tenant;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TenantExporter extends Exporter
{
    protected static ?string $model = Tenant::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('messages.tenant.subdomain') ?: 'Subdomain (ID)'),
            ExportColumn::make('name')
                ->label(__('messages.tenant.workspace_name') ?: 'Workspace Name'),
            ExportColumn::make('email')
                ->label(__('messages.auth.email') ?: 'Contact Email'),
            ExportColumn::make('primary_domain')
                ->label(__('messages.tenant.domain') ?: 'Primary Domain')
                ->state(fn (Tenant $record): ?string => $record->primary_domain),
            ExportColumn::make('status')
                ->label(__('messages.common.status') ?: 'Status')
                ->state(fn (Tenant $record): string => $record->status instanceof \App\Enums\TenantStatus ? $record->status->value : (string) $record->status),
            ExportColumn::make('plan')
                ->label(__('messages.billing.current_plan') ?: 'Plan'),
            ExportColumn::make('trial_ends_at')
                ->label(__('messages.tenant.trial_expiry') ?: 'Trial Expiry'),
            ExportColumn::make('country_code')
                ->label(__('messages.settings.country') ?: 'Country'),
            ExportColumn::make('default_currency')
                ->label(__('messages.settings.currency') ?: 'Currency'),
            ExportColumn::make('default_locale')
                ->label(__('messages.settings.language') ?: 'Locale'),
            ExportColumn::make('timezone')
                ->label(__('messages.settings.timezone') ?: 'Timezone'),
            ExportColumn::make('tax_id')
                ->label(__('messages.tenant.tax_id') ?: 'Tax ID'),
            ExportColumn::make('tax_exempt')
                ->label(__('messages.tenant.tax_exempt') ?: 'Tax Exempt')
                ->state(fn (Tenant $record): string => $record->tax_exempt ? 'Yes' : 'No'),
            ExportColumn::make('created_at')
                ->label(__('messages.common.created_at') ?: 'Created At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('messages.tenant.export_completed', [
            'count' => number_format($export->successful_rows),
            'rows' => str('row')->plural($export->successful_rows),
        ]) ?: ('Your workspace export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.');

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $failedMsg = __('messages.tenant.export_failed_rows', [
                'count' => number_format($failedRowsCount),
                'rows' => str('row')->plural($failedRowsCount),
            ]) ?: (number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.');

            $body .= ' '.$failedMsg;
        }

        return $body;
    }
}
