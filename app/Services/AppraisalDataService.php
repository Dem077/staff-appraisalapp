<?php

namespace App\Services;

use App\Models\AppraisalFormAssignedToStaff;
use App\Models\AppraisalFormEntries;
use App\Models\AppraisalFormKeyBehavior;
use App\Models\AppraisalFormQuestions;
use App\Models\FormsAssignedToHod;
use App\Models\HodFormAssignee;
use App\Models\HodFormAssigneeEntry;
use App\Models\HodFormEntries;

class AppraisalDataService
{
    public static function ratingScale(): array
    {
        return [
            ['label' => '1', 'description' => "Doesn't meet requirements"],
            ['label' => '2', 'description' => 'Meets some requirements'],
            ['label' => '3', 'description' => 'Meets all requirements'],
            ['label' => '4', 'description' => 'Exceeds some requirements, fully met others'],
            ['label' => '5', 'description' => 'Exceeds all requirements'],
        ];
    }

    public static function enrichStaffInfo(AppraisalFormAssignedToStaff $assigned): AppraisalFormAssignedToStaff
    {
        $department = Shortcuts::callgetapi('/users/department', ['id' => $assigned->staff->api_id])->json();
        $supervisor = Shortcuts::callgetapi('/users/supervisor', ['id' => $assigned->staff->api_id])->json();
        $assigned->staff->department = $department['name'] ?? '';
        $assigned->staff->supervisor_name = $supervisor['name'] ?? '';

        return $assigned;
    }

    public static function buildStaffAppraisalData(AppraisalFormAssignedToStaff $assigned): array
    {
        return self::buildAssignmentAppraisalData($assigned, 'staff');
    }

    public static function buildSupervisorAppraisalData(AppraisalFormAssignedToStaff $assigned): array
    {
        return self::buildAssignmentAppraisalData($assigned, 'supervisor');
    }

    protected static function buildAssignmentAppraisalData(AppraisalFormAssignedToStaff $assigned, string $mode): array
    {
        $appraisalData = [];

        foreach ($assigned->appraisalForm->appraisalFormCategories as $category) {
            $categorySection = ['name' => $category->name, 'keyBehaviors' => []];

            foreach ($category->appraisalFormKeyBehaviors as $behavior) {
                $behaviorSection = ['name' => $behavior->name, 'indicators' => []];

                foreach ($behavior->appraisalFormQuestions as $question) {
                    $entry = AppraisalFormEntries::where('appraisal_assigned_to_staff_id', $assigned->id)
                        ->where('question_id', $question->id)
                        ->where('hidden', false)
                        ->first();

                    if ($entry) {
                        $indicator = [
                            'text' => $question->behavioral_indicators,
                            'dhivehi_text' => $question->dhivehi_behavioral_indicators,
                            'question_id' => $question->id,
                            'entry_id' => $entry->id,
                            'self_score' => $entry->staff_score,
                            'supervisor_score' => $entry->supervisor_score,
                            'supervisor_comment' => $entry->supervisor_comment,
                        ];

                        if ($mode === 'staff') {
                            $indicator['self_score'] = $entry->staff_score ?? '';
                        }

                        $behaviorSection['indicators'][] = $indicator;
                    }
                }

                if (count($behaviorSection['indicators'])) {
                    $categorySection['keyBehaviors'][] = $behaviorSection;
                }
            }

            if (count($categorySection['keyBehaviors'])) {
                $appraisalData[] = $categorySection;
            }
        }

        return $appraisalData;
    }

    public static function createEntriesForAssignment(AppraisalFormAssignedToStaff $assigned): void
    {
        $questionIds = AppraisalFormQuestions::whereIn(
            'appraisal_form_key_behavior_id',
            AppraisalFormKeyBehavior::whereIn(
                'appraisal_form_category_id',
                $assigned->appraisalForm->appraisalFormCategories->pluck('id')
            )->pluck('id')
        )->pluck('id');

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

    public static function scoreSummary(AppraisalFormAssignedToStaff $assigned): array
    {
        $entries = AppraisalFormEntries::where('appraisal_assigned_to_staff_id', $assigned->id)
            ->where('hidden', false)
            ->get();

        $staffTotal = $entries->sum('staff_score');
        $staffCount = $entries->whereNotNull('staff_score')->count();
        $supervisorTotal = $entries->sum('supervisor_score');
        $supervisorCount = $entries->whereNotNull('supervisor_score')->count();

        return [
            'staff_average' => $staffCount ? round($staffTotal / $staffCount, 2) : 0,
            'staff_percentage' => $staffCount ? round(($staffTotal / ($staffCount * 5)) * 100, 1) : 0,
            'supervisor_average' => $supervisorCount ? round($supervisorTotal / $supervisorCount, 2) : 0,
            'supervisor_percentage' => $supervisorCount ? round(($supervisorTotal / ($supervisorCount * 5)) * 100, 1) : 0,
            'entries' => $entries->load('question.appraisalFormKeyBehavior.appraisalFormCategory'),
        ];
    }

    public static function buildHodSelfAppraisalData(FormsAssignedToHod $assigned): array
    {
        $appraisalData = [];

        foreach ($assigned->appraisalForm->appraisalFormCategories as $category) {
            $categorySection = ['name' => $category->name, 'keyBehaviors' => []];

            foreach ($category->appraisalFormKeyBehaviors as $behavior) {
                $behaviorSection = ['name' => $behavior->name, 'indicators' => []];

                foreach ($behavior->appraisalFormQuestions as $question) {
                    $entry = HodFormEntries::where('forms_assigned_to_hod_id', $assigned->id)
                        ->where('question_id', $question->id)
                        ->where('hidden', false)
                        ->first();

                    if ($entry) {
                        $behaviorSection['indicators'][] = [
                            'text' => $question->behavioral_indicators,
                            'dhivehi_text' => $question->dhivehi_behavioral_indicators,
                            'question_id' => $question->id,
                            'entry_id' => $entry->id,
                            'self_score' => $entry->self_score ?? '',
                            'comment' => $entry->comment ?? '',
                        ];
                    }
                }

                if (count($behaviorSection['indicators'])) {
                    $categorySection['keyBehaviors'][] = $behaviorSection;
                }
            }

            if (count($categorySection['keyBehaviors'])) {
                $appraisalData[] = $categorySection;
            }
        }

        return $appraisalData;
    }

    public static function buildAssigneeAppraisalData(HodFormAssignee $assignee): array
    {
        $appraisalData = [];
        $hodAssignment = $assignee->formsAssignedToHod;

        foreach ($hodAssignment->appraisalForm->appraisalFormCategories as $category) {
            $categorySection = ['name' => $category->name, 'keyBehaviors' => []];

            foreach ($category->appraisalFormKeyBehaviors as $behavior) {
                $behaviorSection = ['name' => $behavior->name, 'indicators' => []];

                foreach ($behavior->appraisalFormQuestions as $question) {
                    $entry = HodFormAssigneeEntry::where('hod_form_assignee_id', $assignee->id)
                        ->where('question_id', $question->id)
                        ->where('hidden', false)
                        ->first();

                    if ($entry) {
                        $behaviorSection['indicators'][] = [
                            'text' => $question->behavioral_indicators,
                            'dhivehi_text' => $question->dhivehi_behavioral_indicators,
                            'question_id' => $question->id,
                            'entry_id' => $entry->id,
                            'score' => $entry->score ?? '',
                            'comment' => $entry->comment ?? '',
                        ];
                    }
                }

                if (count($behaviorSection['indicators'])) {
                    $categorySection['keyBehaviors'][] = $behaviorSection;
                }
            }

            if (count($categorySection['keyBehaviors'])) {
                $appraisalData[] = $categorySection;
            }
        }

        return $appraisalData;
    }
}
