<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs\Pages;

use App\Models\AppraisalFormQuestions;
use App\Models\AppraisalFormKeyBehavior;
use App\Models\AppraisalFormEntries;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs\AppraisalFormAssignedToStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAppraisalFormAssignedToStaff extends CreateRecord
{
    protected static string $resource = AppraisalFormAssignedToStaffResource::class;

    protected function afterCreate(): void
    {
        $questions = AppraisalFormQuestions::whereIn(
                        'appraisal_form_key_behavior_id',
                        AppraisalFormKeyBehavior::whereIn(
                            'appraisal_form_category_id',
                            $this->record->appraisalForm->appraisalFormCategories->pluck('id')
                        )->pluck('id')
                    )->get();
        foreach ($questions as $question) {
            AppraisalFormEntries::create([
                'appraisal_assigned_to_staff_id' => $this->record->id,
                'question_id' => $question->id,
                'staff_score' => null,
                'supervisor_score' => null,
                'hidden' => false,
            ]);
        }
    }
}
