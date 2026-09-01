<?php

namespace App\Filament\Resources\CentralUserResource\Pages;

use App\Filament\Resources\CentralUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCentralUser extends EditRecord
{
    protected static string $resource = CentralUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
