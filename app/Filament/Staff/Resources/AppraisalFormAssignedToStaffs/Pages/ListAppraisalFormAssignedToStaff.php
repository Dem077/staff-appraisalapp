<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use App\Enum\AppraisalFormLevel;
use App\Enum\AssignedFormStatus;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs\AppraisalFormAssignedToStaffResource;
use App\Models\AppraisalFormAssignedToStaff;
use App\Models\AppraisalFormEntries;
use App\Models\AppraisalFormKeyBehavior;
use App\Models\AppraisalFormQuestions;
use App\Models\Staff;
use App\Services\Shortcuts;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ListAppraisalFormAssignedToStaff extends ListRecords
{
    protected static string $resource = AppraisalFormAssignedToStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulk_create')
                ->label('Assign Form to Staff')
                ->button()
                ->visible(fn() => Auth::user()->can('assign_appraisal_appraisal::form::assigned::to::staff'))
                ->color('success')
                ->schema([
                    Select::make('appraisal_form_id')
                        ->label('Appraisal Form')
                        ->relationship('appraisalForm', 'name' , fn (Builder $query) => $query->where('level', '!=', AppraisalFormLevel::Level3->value))
                        ->required(),
                    Select::make('staff_ids')
                        ->label('Staff')
                        ->multiple()
                        ->options(fn () => $this->groupedStaffOptions())
                        ->preload()
                        ->searchable()
                        ->required(),
                    Select::make('supervisor_id')
                        ->label('Supiervisor')
                        ->options(fn () => $this->groupedStaffOptions())
                        ->preload()
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    foreach ($data['staff_ids'] as $staffId) {

                        $staff = Staff::where('id', $staffId)->first();
                        // Create assignment
                        $assigned = AppraisalFormAssignedToStaff::create([
                            'appraisal_form_id' => $data['appraisal_form_id'],
                            'staff_id' => $staff->id,
                            'supervisor_id' => $data['supervisor_id'],
                            'assigned_date' => now(),
                            'status' => AssignedFormStatus::PendingStaff->value,
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
