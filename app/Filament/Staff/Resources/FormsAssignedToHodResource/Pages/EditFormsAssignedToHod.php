<?php

namespace App\Filament\Staff\Resources\FormsAssignedToHodResource\Pages;

use App\Filament\Staff\Resources\FormsAssignedToHodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFormsAssignedToHod extends EditRecord
{
    protected static string $resource = FormsAssignedToHodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
