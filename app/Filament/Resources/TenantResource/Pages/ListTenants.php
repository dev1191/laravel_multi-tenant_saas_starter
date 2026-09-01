<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Exports\TenantExporter;
use App\Filament\Resources\TenantResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(TenantExporter::class)
                ->icon('heroicon-o-arrow-down-tray')
                ->label(__('messages.tenant.export_csv_xlsx'))
                ->color('gray'),
            CreateAction::make(),
        ];
    }
}
