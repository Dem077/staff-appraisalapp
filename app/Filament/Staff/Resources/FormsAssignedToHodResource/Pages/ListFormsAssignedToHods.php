<?php

namespace App\Filament\Staff\Resources\FormsAssignedToHodResource\Pages;

use App\Filament\Staff\Resources\FormsAssignedToHodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormsAssignedToHods extends ListRecords
{
    protected static string $resource = FormsAssignedToHodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
