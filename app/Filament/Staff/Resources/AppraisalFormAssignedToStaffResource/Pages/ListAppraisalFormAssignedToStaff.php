<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource\Pages;

use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource;
use App\Models\AppraisalFormAssignedToStaff;
use App\Models\AppraisalFormEntries;
use App\Models\AppraisalFormKeyBehavior;
use App\Models\AppraisalFormQuestions;
use App\Models\Staff;
use App\Services\ShortCuts;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListAppraisalFormAssignedToStaff extends ListRecords
{
    protected static string $resource = AppraisalFormAssignedToStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bulk_create')
                ->label('Assign Form to Staff')
                ->button()
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('appraisal_form_id')
                        ->label('Appraisal Form')
                        ->relationship('appraisalForm', 'name')
                        ->required(),
                    \Filament\Forms\Components\Select::make('staff_ids')
                        ->label('Staff')
                        ->multiple()
                        ->options(function () {
                            $authApiId = 4;
                            $supervisor = ShortCuts::callgetapi('/users/staffs', [
                                'id' => $authApiId,
                            ])->json();

                            return $supervisor ? collect($supervisor)->pluck('name', 'id') : collect();
                        })
                        ->preload()
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    foreach ($data['staff_ids'] as $staffId) {

                        $staff = Staff::where('api_id', $staffId)->first();
                        // Create assignment
                        $assigned = AppraisalFormAssignedToStaff::create([
                            'appraisal_form_id' => $data['appraisal_form_id'],
                            'staff_id' => $staff->id,
                            'supervisor_id' => Auth::user()->id,
                            'assigned_date' => now(),
                            'status' => \App\Enum\AssignedFormStatus::PendingStaff->value,
                        ]);

                        // Get all questions for the selected form
                        $questionIds = AppraisalFormQuestions::whereIn(
                            'appraisal_form_key_behavior_id',
                            AppraisalFormKeyBehavior::whereIn(
                                'appraisal_form_category_id',
                                $assigned->appraisalForm->appraisalFormCategories->pluck('id')
                            )->pluck('id')
                        )->pluck('id');

                        // Create entries for each question
                        foreach ($questionIds as $questionId) {
                            AppraisalFormEntries::create([
                                'appraisal_assigned_to_staff_id' => $assigned->id,
                                'question_id' => $questionId,
                                'staff_score' => null,
                                'supervisor_score' => null,
                                'hidden' => false,
                            ]);
                        }
                    }
                })
                ->successNotificationTitle('Bulk assignment and entries created successfully!')
        ];
    }



}
