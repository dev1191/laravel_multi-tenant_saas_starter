<?php

namespace App\Filament\Resources\CentralUserResource\Pages;

use App\Filament\Resources\CentralUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCentralUsers extends ListRecords
{
    protected static string $resource = CentralUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
