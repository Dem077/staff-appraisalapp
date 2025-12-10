<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource\Pages;

use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource;
use App\Services\Shortcuts;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppraisalFormAssignedToStaff extends EditRecord
{
    protected static string $resource = AppraisalFormAssignedToStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible( in_array('HR', Shortcuts::callgetapi('/user/roles', ['id' => auth('staff')->user()->api_id])->json() ?? [])),
            Actions\Action::make('Assigntostaff')
                ->label('Complete and Send to Staff')
                ->button()
                ->color('success')
                ->visible(fn($record) => $record->status === \App\Enum\AssignedFormStatus::PendingQuestionnaireEdit )
                ->action(fn ($record) => $record->update([
                    'status' => \App\Enum\AssignedFormStatus::PendingStaff->value,
                    'assigned_date' => now(),
                ])),
        ];
    }
}
