<?php

namespace App\Filament\Resources\CentralUserResource\Pages;

use App\Filament\Exports\CentralUserExporter;
use App\Filament\Resources\CentralUserResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListCentralUsers extends ListRecords
{
    protected static string $resource = CentralUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(CentralUserExporter::class)
                ->icon('heroicon-o-arrow-down-tray')
                ->label(__('messages.staff.export_csv_xlsx') ?: 'Export CSV / XLSX')
                ->color('gray'),
            CreateAction::make(),
        ];
    }
}
