<?php

namespace App\Http\Controllers;

use App\Enum\AppraisalFormLevel;
use App\Enum\HODFormassigneeStatus;
use App\Enum\HODFormassigneeType;
use App\Models\AppraisalForm;
use App\Models\AppraisalFormKeyBehavior;
use App\Models\AppraisalFormQuestions;
use App\Models\FormsAssignedToHod;
use App\Models\HodFormAssigneeEntry;
use App\Models\HodFormEntries;
use App\Models\HodFormAssignee;
use App\Services\AppraisalDataService;
use App\Services\StaffLookupService;
use App\Support\AuthContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HodAssignmentController extends Controller
{
    public function index(): Response
    {
        $this->authorizeViewAny();

        $staffId = AuthContext::staffId();
        $query = FormsAssignedToHod::with(['appraisalForm', 'staff', 'supervisor', 'hodFormAssignees'])
            ->latest('assigned_date');

        if (! AuthContext::can('view_all_forms::assigned::to::hod')) {
            $query->where(function ($q) use ($staffId) {
                $q->where('hod_id', $staffId)
                    ->orWhere('supervisor_id', $staffId)
                    ->orWhereHas('hodFormAssignees', fn ($aq) => $aq->where('assignee_id', $staffId));
            });
        }

        $assignments = $query->paginate(15)->through(function ($record) {
            $data = $this->transform($record);
            $staffId = AuthContext::staffId();
            $myAssignee = $record->hodFormAssignees->firstWhere('assignee_id', $staffId);
            if ($myAssignee) {
                $data['my_assignee_id'] = $myAssignee->id;
                $data['actions']['fill_assignee'] = empty($myAssignee->assignee_comment);
            }

            return $data;
        });

        return Inertia::render('HodAssignments/Index', [
            'assignments' => $assignments,
            'canAssign' => AuthContext::can('assign_appraisal_appraisal::form::assigned::to::staff'),
        ]);
    }

    public function create(): Response
    {
        abort_unless(AuthContext::can('assign_appraisal_appraisal::form::assigned::to::staff'), 403);

        return Inertia::render('HodAssignments/Create', [
            'forms' => AppraisalForm::where('level', AppraisalFormLevel::Level3->value)
                ->where('is_active', true)
                ->pluck('name', 'id'),
            'staffOptions' => StaffLookupService::groupedStaffOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(AuthContext::can('assign_appraisal_appraisal::form::assigned::to::staff'), 403);

        $data = $request->validate([
            'appraisal_form_id' => ['required', 'exists:appraisal_forms,id'],
            'hod_id' => ['required', 'exists:staff,id'],
            'supervisor_id' => ['required', 'exists:staff,id'],
            'managers_id' => ['required', 'array', 'size:3'],
            'co_workers_id' => ['required', 'array', 'size:3'],
            'subordinates_id' => ['required', 'array', 'size:3'],
            'managers_id.*' => ['exists:staff,id'],
            'co_workers_id.*' => ['exists:staff,id'],
            'subordinates_id.*' => ['exists:staff,id'],
        ]);

        $hodForm = FormsAssignedToHod::create([
            'assigned_date' => now(),
            'appraisal_form_id' => $data['appraisal_form_id'],
            'hod_id' => $data['hod_id'],
            'supervisor_id' => $data['supervisor_id'],
            'status' => HODFormassigneeStatus::PendingStaff->value,
        ]);

        $hodForm->load('appraisalForm.appraisalFormCategories');
        $this->createHodEntries($hodForm);
        $this->createAssignees($hodForm, $data['managers_id'], HODFormassigneeType::Manager);
        $this->createAssignees($hodForm, $data['co_workers_id'], HODFormassigneeType::CoWorker);
        $this->createAssignees($hodForm, $data['subordinates_id'], HODFormassigneeType::Subordinate);

        return redirect()->route('hod-assignments.index')
            ->with('success', 'HOD appraisal created successfully.');
    }

    public function fillHod(FormsAssignedToHod $hodAssignment): Response
    {
        abort_unless(
            $hodAssignment->status === HODFormassigneeStatus::PendingStaff
            && AuthContext::staffId() === $hodAssignment->hod_id,
            403
        );

        $hodAssignment->load(['appraisalForm.appraisalFormCategories.appraisalFormKeyBehaviors.appraisalFormQuestions', 'staff']);

        return Inertia::render('HodAssignments/FillHod', [
            'assignment' => $this->transform($hodAssignment),
            'hod' => $hodAssignment->staff,
            'ratingScale' => AppraisalDataService::ratingScale(),
            'appraisalData' => AppraisalDataService::buildHodSelfAppraisalData($hodAssignment),
        ]);
    }

    public function submitHod(Request $request, FormsAssignedToHod $hodAssignment): RedirectResponse
    {
        abort_unless(
            $hodAssignment->status === HODFormassigneeStatus::PendingStaff
            && AuthContext::staffId() === $hodAssignment->hod_id,
            403
        );

        $data = $request->validate([
            'scores' => ['required', 'array'],
            'scores.*.question_id' => ['required', 'exists:appraisal_form_questions,id'],
            'scores.*.self_score' => ['required', 'integer', 'min:1', 'max:5'],
            'hod_comment' => ['nullable', 'string'],
        ]);

        foreach ($data['scores'] as $score) {
            HodFormEntries::where('forms_assigned_to_hod_id', $hodAssignment->id)
                ->where('question_id', $score['question_id'])
                ->update(['self_score' => $score['self_score']]);
        }

        $hodAssignment->update([
            'hod_comment' => $data['hod_comment'] ?? '',
            'status' => HODFormassigneeStatus::PendingAssignee->value,
        ]);

        return redirect()->route('hod-assignments.index')
            ->with('success', 'HOD self-appraisal submitted.');
    }

    public function fillAssignee(HodFormAssignee $assignee): Response
    {
        abort_unless(
            AuthContext::staffId() === $assignee->assignee_id,
            403
        );

        $assignee->load(['formsAssignedToHod.appraisalForm.appraisalFormCategories.appraisalFormKeyBehaviors.appraisalFormQuestions', 'staff']);

        return Inertia::render('HodAssignments/FillAssignee', [
            'assignee' => [
                'id' => $assignee->id,
                'type' => $assignee->assignee_type?->getLabel(),
                'staff' => $assignee->staff?->only(['id', 'name', 'emp_no', 'designation']),
            ],
            'hodAssignment' => $this->transform($assignee->formsAssignedToHod),
            'ratingScale' => AppraisalDataService::ratingScale(),
            'appraisalData' => AppraisalDataService::buildAssigneeAppraisalData($assignee),
        ]);
    }

    public function submitAssignee(Request $request, HodFormAssignee $assignee): RedirectResponse
    {
        abort_unless(AuthContext::staffId() === $assignee->assignee_id, 403);

        $data = $request->validate([
            'scores' => ['required', 'array'],
            'scores.*.question_id' => ['required', 'exists:appraisal_form_questions,id'],
            'scores.*.score' => ['required', 'integer', 'min:1', 'max:5'],
            'assignee_comment' => ['nullable', 'string'],
        ]);

        foreach ($data['scores'] as $score) {
            HodFormAssigneeEntry::where('hod_form_assignee_id', $assignee->id)
                ->where('question_id', $score['question_id'])
                ->update(['score' => $score['score']]);
        }

        $assignee->update(['assignee_comment' => $data['assignee_comment'] ?? '']);

        $hodAssignment = $assignee->formsAssignedToHod;
        $allDone = $hodAssignment->hodFormAssignees()
            ->whereNull('assignee_comment')
            ->where('id', '!=', $assignee->id)
            ->doesntExist();

        if ($allDone) {
            $hodAssignment->update(['status' => HODFormassigneeStatus::HRComment->value]);
        }

        return redirect()->route('hod-assignments.index')
            ->with('success', '360 feedback submitted.');
    }

    public function results(FormsAssignedToHod $hodAssignment): Response
    {
        $this->authorizeView($hodAssignment);

        $hodAssignment->load(['appraisalForm', 'staff', 'supervisor', 'hodFormAssignees.staff', 'hodFormAssignees.hodFormAssigneeEntries', 'hodFormEntries']);

        return Inertia::render('HodAssignments/Results', [
            'assignment' => $this->transform($hodAssignment),
            'hodScore' => $hodAssignment->hodFormEntries->where('hidden', false)->sum('self_score'),
            'assignees' => $hodAssignment->hodFormAssignees->map(fn ($a) => [
                'id' => $a->id,
                'type' => $a->assignee_type?->getLabel(),
                'name' => $a->staff?->name,
                'score' => $a->hodFormAssigneeEntries->where('hidden', false)->sum('score'),
                'comment' => $a->assignee_comment,
            ]),
            'canAddHrComment' => $hodAssignment->status === HODFormassigneeStatus::HRComment && AuthContext::isHr(),
        ]);
    }

    public function hrComment(Request $request, FormsAssignedToHod $hodAssignment): RedirectResponse
    {
        abort_unless($hodAssignment->status === HODFormassigneeStatus::HRComment && AuthContext::isHr(), 403);

        $data = $request->validate(['hr_comment' => ['required', 'string']]);

        $hodAssignment->update([
            'hr_comment' => $data['hr_comment'],
            'status' => HODFormassigneeStatus::Completed->value,
        ]);

        return back()->with('success', 'HR comment added.');
    }

    protected function createHodEntries(FormsAssignedToHod $hodForm): void
    {
        $questionIds = $this->questionIdsForForm($hodForm);

        foreach ($questionIds as $questionId) {
            HodFormEntries::create([
                'forms_assigned_to_hod_id' => $hodForm->id,
                'question_id' => $questionId,
            ]);
        }
    }

    protected function createAssignees(FormsAssignedToHod $hodForm, array $staffIds, HODFormassigneeType $type): void
    {
        $questionIds = $this->questionIdsForForm($hodForm);

        foreach ($staffIds as $staffId) {
            $assignee = $hodForm->hodFormAssignees()->create([
                'assignee_id' => $staffId,
                'assignee_type' => $type->value,
            ]);

            foreach ($questionIds as $questionId) {
                HodFormAssigneeEntry::create([
                    'hod_form_assignee_id' => $assignee->id,
                    'question_id' => $questionId,
                ]);
            }
        }
    }

    protected function questionIdsForForm(FormsAssignedToHod $hodForm): \Illuminate\Support\Collection
    {
        return AppraisalFormQuestions::whereIn(
            'appraisal_form_key_behavior_id',
            AppraisalFormKeyBehavior::whereIn(
                'appraisal_form_category_id',
                $hodForm->appraisalForm->appraisalFormCategories->pluck('id')
            )->pluck('id')
        )->pluck('id');
    }

    protected function transform(FormsAssignedToHod $assignment): array
    {
        $status = $assignment->status;
        $staffId = AuthContext::staffId();

        return [
            'id' => $assignment->id,
            'assigned_date' => $assignment->assigned_date,
            'status' => $status instanceof HODFormassigneeStatus ? $status->value : $status,
            'status_label' => $status instanceof HODFormassigneeStatus ? $status->getLabel() : $status,
            'status_color' => $status instanceof HODFormassigneeStatus ? $status->getColor() : 'gray',
            'hod_comment' => $assignment->hod_comment,
            'supervisor_comment' => $assignment->supervisor_comment,
            'hr_comment' => $assignment->hr_comment,
            'form' => $assignment->appraisalForm?->only(['id', 'name']),
            'hod' => $assignment->staff?->only(['id', 'name', 'emp_no']),
            'supervisor' => $assignment->supervisor?->only(['id', 'name']),
            'actions' => [
                'fill_hod' => $status === HODFormassigneeStatus::PendingStaff && $assignment->hod_id === $staffId,
                'results' => $status !== HODFormassigneeStatus::PendingStaff,
            ],
        ];
    }

    protected function authorizeViewAny(): void
    {
        abort_unless(AuthContext::canAny([
            'view_any_forms::assigned::to::hod',
            'view_forms::assigned::to::hod',
        ]), 403);
    }

    protected function authorizeView(FormsAssignedToHod $assignment): void
    {
        if (AuthContext::can('view_all_forms::assigned::to::hod')) {
            return;
        }

        $staffId = AuthContext::staffId();
        abort_unless(
            $assignment->hod_id === $staffId
            || $assignment->supervisor_id === $staffId
            || $assignment->hodFormAssignees()->where('assignee_id', $staffId)->exists(),
            403
        );
    }
}
