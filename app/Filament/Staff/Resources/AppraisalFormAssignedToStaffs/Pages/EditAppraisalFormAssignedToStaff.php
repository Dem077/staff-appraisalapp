<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs\AppraisalFormAssignedToStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppraisalFormAssignedToStaff extends EditRecord
{
    protected static string $resource = AppraisalFormAssignedToStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
