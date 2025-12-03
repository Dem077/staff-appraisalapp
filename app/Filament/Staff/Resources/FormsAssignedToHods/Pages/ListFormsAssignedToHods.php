<?php

namespace App\Filament\Staff\Resources\FormsAssignedToHods\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use App\Enum\AppraisalFormLevel;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use App\Enum\HODFormassigneeType;
use App\Filament\Staff\Resources\FormsAssignedToHods\FormsAssignedToHodResource;
use App\Models\AppraisalFormKeyBehavior;
use App\Models\AppraisalFormQuestions;
use App\Models\FormsAssignedToHod;
use App\Models\HodFormAssigneeEntry;
use App\Models\HodFormEntries;
use App\Models\Staff;
use App\Services\Shortcuts;
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ListFormsAssignedToHods extends ListRecords
{
    protected static string $resource = FormsAssignedToHodResource::class;

    protected array $activeuser = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulk_create')
                ->label('Create Form for Staff')
                ->button()
                ->visible(fn() => Auth::user()->can('assign_appraisal_appraisal::form::assigned::to::staff'))
                ->color('success')
                ->schema([
                    Select::make('appraisal_form_id')
                        ->label('Appraisal Form')
                        ->relationship('appraisalForm', 'name' , fn (Builder $query) => $query->where('level', AppraisalFormLevel::Level3->value))
                        ->required()
                        ->native(false),
                    Select::make('staff_id')
                        ->label('For Staff')
                        ->options(fn () => $this->groupedStaffOptions())
                        ->preload()
                        ->afterStateUpdated(function ($state, Set $set) {
                            // store first selected for subordinates lookup
                            if (is_array($state) && count($state)) {
                                $set('selected_staff', reset($state));
                            } else {
                                $set('selected_staff', $state ?: null);
                            }
                        })
                        ->searchable()
                        ->live()
                        ->reactive()
                        ->required(),
                    Select::make('supervisor_id')
                        ->relationship('supervisor', 'name')
                        ->options(fn () => $this->groupedStaffOptions())
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->required()
                        ->reactive()
                        ->live(),
                    Section::make('Asignees')
                    ->description('Select 3 from each type')
                        ->schema([
                            Select::make('managers_id')
                                ->label('Managers')
                                ->multiple()
                                ->options(fn () => $this->groupedStaffOptions())
                                ->preload()
                                ->maxItems(3)
                                ->searchable()
                                ->required(),
                            Select::make('co_workers_id')
                                ->label('Co-Workers')
                                ->multiple()
                                ->options(fn () => $this->groupedStaffOptions())
                                ->preload()
                                ->maxItems(3)
                                ->searchable()
                                ->required(),
                            Select::make('subordinates_id')
                                ->label('Subordinates')
                                ->multiple()
                                ->live()
                                ->options(fn () => $this->groupedStaffOptions())
                                ->maxItems(3)
                                ->reactive()
                                ->searchable()
                                ->required(),
                        ])
                ])
                ->action(function (array $data) {

                        $staff = Staff::where('id', $data['staff_id'])->first();

                        $assignedhodform = FormsAssignedToHod::create([
                                'assigned_date' => now(),
                                'appraisal_form_id' => $data['appraisal_form_id'],
                                'hod_id' => $staff->id,
                                'supervisor_id' => $data['supervisor_id'],
                            ]);

                        // Get all questions for the selected form
                        $questionIds = AppraisalFormQuestions::whereIn(
                            'appraisal_form_key_behavior_id',
                            AppraisalFormKeyBehavior::whereIn(
                                'appraisal_form_category_id',
                                $assignedhodform->appraisalForm->appraisalFormCategories->pluck('id')
                            )->pluck('id')
                        )->pluck('id');

                        // Create entries for each question
                        foreach ($questionIds as $questionId) {
                            HodFormEntries::create([
                                'forms_assigned_to_hod_id' => $assignedhodform->id,
                                'question_id' => $questionId,
                            ]);
                        }


                        $managerids= $data['managers_id'];
                        $coworkerids= $data['co_workers_id'];
                        $subordinateids= $data['subordinates_id'];


                        //FOR MANAGERS


                        foreach ($managerids as $managerId) {
                           $manager = Staff::where('id', $managerId)->first();

                           $hodassigned = $assignedhodform->hodFormAssignees()->create([
                                'assignee_id' => $manager->id,
                                'assignee_type' => HODFormassigneeType::Manager->value,
                            ]);

                            // Get all questions for the selected form
                            $questionIds = AppraisalFormQuestions::whereIn(
                                'appraisal_form_key_behavior_id',
                                AppraisalFormKeyBehavior::whereIn(
                                    'appraisal_form_category_id',
                                    $assignedhodform->appraisalForm->appraisalFormCategories->pluck('id')
                                )->pluck('id')
                            )->pluck('id');

                            // Create entries for each question
                            foreach ($questionIds as $questionId) {
                                HodFormAssigneeEntry::create([
                                    'hod_form_assignee_id' => $hodassigned->id,
                                    'question_id' => $questionId,
                                ]);

                            }
                        }


                        //FOR COWORKERS


                        foreach ($coworkerids as $coworkerid) {
                           $coworker = Staff::where('id', $coworkerid)->first();

                           $hodassigned = $assignedhodform->hodFormAssignees()->create([
                                'assignee_id' => $coworker->id,
                                'assignee_type' => HODFormassigneeType::CoWorker->value,
                            ]);

                            // Get all questions for the selected form
                            $questionIds = AppraisalFormQuestions::whereIn(
                                'appraisal_form_key_behavior_id',
                                AppraisalFormKeyBehavior::whereIn(
                                    'appraisal_form_category_id',
                                    $assignedhodform->appraisalForm->appraisalFormCategories->pluck('id')
                                )->pluck('id')
                            )->pluck('id');

                            // Create entries for each question
                            foreach ($questionIds as $questionId) {
                                HodFormAssigneeEntry::create([
                                    'hod_form_assignee_id' => $hodassigned->id,
                                    'question_id' => $questionId,
                                ]);

                            }
                        }


                        //FOR SUBORDINATES


                        foreach ($subordinateids as $subordinateid) {
                           $subordinate = Staff::where('id', $subordinateid)->first();

                           $hodassigned = $assignedhodform->hodFormAssignees()->create([
                                'assignee_id' => $subordinate->id,
                                'assignee_type' => HODFormassigneeType::Subordinate->value,
                            ]);

                            // Get all questions for the selected form
                            $questionIds = AppraisalFormQuestions::whereIn(
                                'appraisal_form_key_behavior_id',
                                AppraisalFormKeyBehavior::whereIn(
                                    'appraisal_form_category_id',
                                    $assignedhodform->appraisalForm->appraisalFormCategories->pluck('id')
                                )->pluck('id')
                            )->pluck('id');

                            // Create entries for each question
                            foreach ($questionIds as $questionId) {
                                HodFormAssigneeEntry::create([
                                    'hod_form_assignee_id' => $hodassigned->id,
                                    'question_id' => $questionId,
                                ]);

                            }
                        }

                })
                ->successNotificationTitle('Bulk assignment and entries created successfully!')
        ];
    }

    protected function groupedStaffOptions(): array
    {
        return Cache::remember('grouped_staff_options_v1', 60, function () {
            $all = Shortcuts::callgetapi('/users/active', [])->json();
            if (! is_array($all)) {
                return [];
            }

            $deptNameCache = [];

            return collect($all)
                ->filter(fn ($u) => is_array($u) && isset($u['id']))
                ->groupBy(function ($u) use (&$deptNameCache) {
                    $deptId = $u['department_id'] ?? null;
                    if (! $deptId) {
                        return 'Unknown Department';
                    }
                    if (! array_key_exists($deptId, $deptNameCache)) {
                        $resp = Shortcuts::callgetapi('/department', ['dep_id' => $deptId])->json();
                        $deptNameCache[$deptId] = (is_array($resp) && isset($resp['name']))
                            ? (string)$resp['name']
                            : 'Unknown Department';
                    }
                    return $deptNameCache[$deptId];
                })
                ->map(function ($group) {
                    return collect($group)
                        ->filter(fn ($u) => isset($u['name']) && $u['name'] !== '')
                        ->mapWithKeys(function ($u) {
                            $actualid = Staff::where('api_id', $u['id'])->pluck('id')->first();
                            $label = (string)$u['name'];
                            if (! empty($u['emp_no'])) {
                                $label .= ' (' . $u['emp_no'] . ')';
                            }
                            return [$actualid => $label];
                        })
                        ->toArray();
                })
                ->toArray();
        });
    }
}
