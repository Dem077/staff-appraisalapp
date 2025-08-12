<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource\Pages;

use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource;
use App\Models\AppraisalFormEntries;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppraisalFormAssignedToStaff extends ListRecords
{
    protected static string $resource = AppraisalFormAssignedToStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }



}
