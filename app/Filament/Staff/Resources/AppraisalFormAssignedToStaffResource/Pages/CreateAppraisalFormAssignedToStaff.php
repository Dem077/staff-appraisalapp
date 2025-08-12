<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource\Pages;

use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAppraisalFormAssignedToStaff extends CreateRecord
{
    protected static string $resource = AppraisalFormAssignedToStaffResource::class;

        protected function afterCreate(): void
    {
        $questions = \App\Models\AppraisalFormQuestions::whereIn(
                        'appraisal_form_key_behavior_id',
                        \App\Models\AppraisalFormKeyBehavior::whereIn(
                            'appraisal_form_category_id',
                            $this->record->appraisalForm->appraisalFormCategories->pluck('id')
                        )->pluck('id')
                    )->get();
        foreach ($questions as $question) {
            \App\Models\AppraisalFormEntries::create([
                'appraisal_assigned_to_staff_id' => $this->record->id,
                'question_id' => $question->id,
                'staff_score' => null,
                'supervisor_score' => null,
                'hidden' => false,
            ]);
        }
    }
}
