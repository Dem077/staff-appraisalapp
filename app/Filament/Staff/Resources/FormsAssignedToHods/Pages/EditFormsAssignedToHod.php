<?php

namespace App\Filament\Staff\Resources\FormsAssignedToHods\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Staff\Resources\FormsAssignedToHods\FormsAssignedToHodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFormsAssignedToHod extends EditRecord
{
    protected static string $resource = FormsAssignedToHodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
