<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource\Pages;

use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppraisalFormAssignedToStaff extends EditRecord
{
    protected static string $resource = AppraisalFormAssignedToStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
