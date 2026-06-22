<?php

namespace App\Http\Controllers;

use App\Enum\AppraisalFormLevel;
use App\Enum\AssignedFormStatus;
use App\Models\AppraisalForm;
use App\Models\AppraisalFormAssignedToStaff;
use App\Models\AppraisalFormEntries;
use App\Models\Staff;
use App\Services\AppraisalDataService;
use App\Services\AppraisalPdfService;
use App\Services\StaffLookupService;
use App\Support\AuthContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppraisalAssignmentController extends Controller
{
    public function __construct(
        protected AppraisalPdfService $pdfService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorizeViewAny();

        $staffId = AuthContext::staffId();
        $tab = $request->string('tab')->toString() ?: 'all';

        $query = AppraisalFormAssignedToStaff::with(['appraisalForm', 'staff', 'supervisor'])
            ->latest('assigned_date');

        if (! AuthContext::can('view_all_appraisal::form::assigned::to::staff')) {
            $query->where(function ($q) use ($staffId) {
                $q->where('supervisor_id', $staffId)
                    ->orWhere('staff_id', $staffId);
            });
        }

        $tabs = $this->assignmentTabs($query);
        $tabKeys = array_keys($tabs);

        $filteredQuery = match ($tab) {
            'questionnaire_edit' => (clone $query)->where('status', AssignedFormStatus::PendingQuestionnaireEdit->value),
            'staff_fill' => (clone $query)->where('status', AssignedFormStatus::PendingStaff->value),
            'supervisor_review' => (clone $query)->where('status', AssignedFormStatus::PendingSupervisor->value),
            'hr_review' => (clone $query)->where('status', AssignedFormStatus::HRComment->value),
            'completed' => (clone $query)->where('status', AssignedFormStatus::Complete->value),
            default => clone $query,
        };

        $assignments = $filteredQuery
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($record) => $this->transformAssignment($record));

        return Inertia::render('Assignments/Index', [
            'assignments' => $assignments,
            'canAssign' => AuthContext::can('assign_appraisal_appraisal::form::assigned::to::staff'),
            'tabs' => $tabs,
            'activeTab' => in_array($tab, $tabKeys, true) ? $tab : 'all',
        ]);
    }

    protected function assignmentTabs($query): array
    {
        $stages = [
            'all' => ['label' => 'All', 'status' => null],
            'questionnaire_edit' => ['label' => 'Questionnaire setup', 'status' => AssignedFormStatus::PendingQuestionnaireEdit],
            'staff_fill' => ['label' => 'User fill', 'status' => AssignedFormStatus::PendingStaff],
            'supervisor_review' => ['label' => 'Supervisor review', 'status' => AssignedFormStatus::PendingSupervisor],
            'hr_review' => ['label' => 'HR review', 'status' => AssignedFormStatus::HRComment],
            'completed' => ['label' => 'Completed', 'status' => AssignedFormStatus::Complete],
        ];

        $tabs = [];

        foreach ($stages as $key => $stage) {
            $countQuery = clone $query;

            if ($stage['status']) {
                $countQuery->where('status', $stage['status']->value);
            }

            $tabs[$key] = [
                'key' => $key,
                'label' => $stage['label'],
                'count' => $countQuery->count(),
            ];
        }

        return $tabs;
    }

    public function create(): Response
    {
        $this->authorizeAssign();

        return Inertia::render('Assignments/Create', [
            'forms' => AppraisalForm::where('level', '!=', AppraisalFormLevel::Level3->value)
                ->where('is_active', true)
                ->pluck('name', 'id'),
            'staffOptions' => StaffLookupService::groupedStaffOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAssign();

        $data = $request->validate([
            'appraisal_form_id' => ['required', 'exists:appraisal_forms,id'],
            'staff_ids' => ['required', 'array', 'min:1'],
            'staff_ids.*' => ['exists:staff,id'],
            'supervisor_id' => ['required', 'exists:staff,id'],
        ]);

        foreach ($data['staff_ids'] as $staffId) {
            $assigned = AppraisalFormAssignedToStaff::create([
                'appraisal_form_id' => $data['appraisal_form_id'],
                'staff_id' => $staffId,
                'supervisor_id' => $data['supervisor_id'],
                'assigned_date' => now(),
                'status' => AssignedFormStatus::PendingQuestionnaireEdit->value,
            ]);

            $assigned->load('appraisalForm.appraisalFormCategories');
            AppraisalDataService::createEntriesForAssignment($assigned);
        }

        return redirect()->route('assignments.index')
            ->with('success', 'Appraisal assignments created successfully.');
    }

    public function edit(AppraisalFormAssignedToStaff $assignment): Response
    {
        $this->authorizeView($assignment);

        $assignment->load(['appraisalForm', 'staff', 'supervisor', 'appraisalFormEntries.question.appraisalFormKeyBehavior.appraisalFormCategory']);

        return Inertia::render('Assignments/Edit', [
            'assignment' => $this->transformAssignment($assignment),
            'entries' => $assignment->appraisalFormEntries->map(fn ($entry) => [
                'id' => $entry->id,
                'hidden' => $entry->hidden,
                'indicator' => $entry->question?->behavioral_indicators,
                'dhivehi' => $entry->question?->dhivehi_behavioral_indicators,
                'key_behavior' => $entry->question?->appraisalFormKeyBehavior?->name,
                'category' => $entry->question?->appraisalFormKeyBehavior?->appraisalFormCategory?->name,
            ]),
            'canSendToStaff' => $assignment->status === AssignedFormStatus::PendingQuestionnaireEdit,
            'canDelete' => AuthContext::isHr(),
        ]);
    }

    public function sendToStaff(AppraisalFormAssignedToStaff $assignment): RedirectResponse
    {
        $this->authorizeView($assignment);

        abort_unless($assignment->status === AssignedFormStatus::PendingQuestionnaireEdit, 403);

        $assignment->update([
            'status' => AssignedFormStatus::PendingStaff->value,
            'assigned_date' => now(),
        ]);

        return back()->with('success', 'Appraisal sent to staff.');
    }

    public function syncEntries(AppraisalFormAssignedToStaff $assignment): RedirectResponse
    {
        $this->authorizeView($assignment);

        AppraisalDataService::createEntriesForAssignment($assignment->load('appraisalForm.appraisalFormCategories'));

        return back()->with('success', 'Indicators synced.');
    }

    public function updateEntries(Request $request, AppraisalFormAssignedToStaff $assignment): RedirectResponse
    {
        $this->authorizeView($assignment);

        $data = $request->validate([
            'entries' => ['required', 'array'],
            'entries.*.id' => ['required', 'exists:appraisal_form_entries,id'],
            'entries.*.hidden' => ['required', 'boolean'],
        ]);

        foreach ($data['entries'] as $entryData) {
            AppraisalFormEntries::where('id', $entryData['id'])
                ->where('appraisal_assigned_to_staff_id', $assignment->id)
                ->update(['hidden' => $entryData['hidden']]);
        }

        return back()->with('success', 'Questionnaire updated.');
    }

    public function destroy(AppraisalFormAssignedToStaff $assignment): RedirectResponse
    {
        abort_unless(AuthContext::isHr(), 403);

        $assignment->delete();

        return redirect()->route('assignments.index')
            ->with('success', 'Assignment deleted.');
    }

    public function fillStaff(AppraisalFormAssignedToStaff $assignment): Response
    {
        abort_unless(
            $assignment->status === AssignedFormStatus::PendingStaff
            && AuthContext::staffId() === $assignment->staff_id,
            403
        );

        $assignment->load(['appraisalForm.appraisalFormCategories.appraisalFormKeyBehaviors.appraisalFormQuestions', 'staff']);
        AppraisalDataService::enrichStaffInfo($assignment);

        return Inertia::render('Assignments/FillStaff', [
            'assignment' => $this->transformAssignment($assignment),
            'staff' => $assignment->staff,
            'ratingScale' => AppraisalDataService::ratingScale(),
            'appraisalData' => AppraisalDataService::buildStaffAppraisalData($assignment),
        ]);
    }

    public function submitStaff(Request $request, AppraisalFormAssignedToStaff $assignment): RedirectResponse
    {
        abort_unless(
            $assignment->status === AssignedFormStatus::PendingStaff
            && AuthContext::staffId() === $assignment->staff_id,
            403
        );

        $data = $request->validate([
            'scores' => ['required', 'array'],
            'scores.*.question_id' => ['required', 'exists:appraisal_form_questions,id'],
            'scores.*.self_score' => ['required', 'integer', 'min:1', 'max:5'],
            'employee_comments' => ['nullable', 'string'],
        ]);

        foreach ($data['scores'] as $score) {
            AppraisalFormEntries::where('appraisal_assigned_to_staff_id', $assignment->id)
                ->where('question_id', $score['question_id'])
                ->update(['staff_score' => $score['self_score']]);
        }

        $assignment->update([
            'staff_comment' => $data['employee_comments'] ?? '',
            'status' => AssignedFormStatus::PendingSupervisor->value,
        ]);

        return redirect()->route('assignments.index')
            ->with('success', 'Appraisal submitted successfully.');
    }

    public function fillSupervisor(AppraisalFormAssignedToStaff $assignment): Response
    {
        abort_unless(
            $assignment->status === AssignedFormStatus::PendingSupervisor
            && AuthContext::staffId() === $assignment->supervisor_id,
            403
        );

        $assignment->load(['appraisalForm.appraisalFormCategories.appraisalFormKeyBehaviors.appraisalFormQuestions', 'staff']);
        AppraisalDataService::enrichStaffInfo($assignment);

        return Inertia::render('Assignments/FillSupervisor', [
            'assignment' => $this->transformAssignment($assignment),
            'staff' => $assignment->staff,
            'ratingScale' => AppraisalDataService::ratingScale(),
            'appraisalData' => AppraisalDataService::buildSupervisorAppraisalData($assignment),
        ]);
    }

    public function submitSupervisor(Request $request, AppraisalFormAssignedToStaff $assignment): RedirectResponse
    {
        abort_unless(
            $assignment->status === AssignedFormStatus::PendingSupervisor
            && AuthContext::staffId() === $assignment->supervisor_id,
            403
        );

        $data = $request->validate([
            'scores' => ['required', 'array'],
            'scores.*.question_id' => ['required', 'exists:appraisal_form_questions,id'],
            'scores.*.supervisor_score' => ['required', 'integer', 'min:1', 'max:5'],
            'scores.*.supervisor_comment' => ['nullable', 'string'],
            'supervisor_comments' => ['nullable', 'string'],
        ]);

        foreach ($data['scores'] as $score) {
            AppraisalFormEntries::where('appraisal_assigned_to_staff_id', $assignment->id)
                ->where('question_id', $score['question_id'])
                ->update([
                    'supervisor_score' => $score['supervisor_score'],
                    'supervisor_comment' => $score['supervisor_comment'] ?? null,
                ]);
        }

        $assignment->update([
            'supervisor_comment' => $data['supervisor_comments'] ?? '',
            'status' => AssignedFormStatus::HRComment->value,
        ]);

        return redirect()->route('assignments.index')
            ->with('success', 'Supervisor appraisal submitted.');
    }

    public function results(AppraisalFormAssignedToStaff $assignment): Response
    {
        $this->authorizeResults($assignment);

        $summary = AppraisalDataService::scoreSummary($assignment);
        $assignment->load(['appraisalForm', 'staff', 'supervisor']);

        return Inertia::render('Assignments/Results', [
            'assignment' => $this->transformAssignment($assignment),
            'summary' => [
                'staff_average' => $summary['staff_average'],
                'staff_percentage' => $summary['staff_percentage'],
                'supervisor_average' => $summary['supervisor_average'],
                'supervisor_percentage' => $summary['supervisor_percentage'],
            ],
            'entries' => $summary['entries']->map(fn ($entry) => [
                'category' => $entry->question?->appraisalFormKeyBehavior?->appraisalFormCategory?->name,
                'key_behavior' => $entry->question?->appraisalFormKeyBehavior?->name,
                'indicator' => $entry->question?->behavioral_indicators,
                'dhivehi' => $entry->question?->dhivehi_behavioral_indicators,
                'staff_score' => $entry->staff_score,
                'supervisor_score' => $entry->supervisor_score,
                'supervisor_comment' => $entry->supervisor_comment,
            ]),
            'canAddHrComment' => $assignment->status === AssignedFormStatus::HRComment && AuthContext::isHr(),
            'canDownloadPdf' => $assignment->status === AssignedFormStatus::Complete,
        ]);
    }

    public function hrComment(Request $request, AppraisalFormAssignedToStaff $assignment): RedirectResponse
    {
        abort_unless($assignment->status === AssignedFormStatus::HRComment && AuthContext::isHr(), 403);

        $data = $request->validate([
            'hr_comment' => ['required', 'string'],
        ]);

        $assignment->update([
            'hr_comment' => $data['hr_comment'],
            'status' => AssignedFormStatus::Complete->value,
        ]);

        return back()->with('success', 'HR comment added.');
    }

    public function pdf(Request $request, AppraisalFormAssignedToStaff $assignment)
    {
        $this->authorizeResults($assignment);
        abort_unless($assignment->status === AssignedFormStatus::Complete, 403);

        $assignment->load(['appraisalForm', 'staff', 'supervisor']);
        $summary = AppraisalDataService::scoreSummary($assignment);

        return $this->pdfService->download(
            $assignment,
            $summary['entries'],
            $summary,
            $request->boolean('download'),
        );
    }

    public function resolveSupervisor(Request $request)
    {
        $request->validate(['staff_id' => ['required', 'exists:staff,id']]);

        return response()->json([
            'supervisor_id' => StaffLookupService::resolveSupervisorId($request->integer('staff_id')),
        ]);
    }

    protected function transformAssignment(AppraisalFormAssignedToStaff $assignment): array
    {
        $status = $assignment->status;

        return [
            'id' => $assignment->id,
            'assigned_date' => $assignment->assigned_date,
            'status' => $status instanceof AssignedFormStatus ? $status->value : $status,
            'status_label' => $status instanceof AssignedFormStatus ? $status->getLabel() : $status,
            'status_color' => $status instanceof AssignedFormStatus ? $status->getColor() : 'gray',
            'staff_comment' => $assignment->staff_comment,
            'supervisor_comment' => $assignment->supervisor_comment,
            'hr_comment' => $assignment->hr_comment,
            'form' => $assignment->appraisalForm?->only(['id', 'name', 'type', 'level']),
            'staff' => $assignment->staff?->only(['id', 'name', 'emp_no', 'designation', 'nid']),
            'supervisor' => $assignment->supervisor?->only(['id', 'name', 'emp_no']),
            'actions' => $this->availableActions($assignment),
        ];
    }

    protected function availableActions(AppraisalFormAssignedToStaff $assignment): array
    {
        $staffId = AuthContext::staffId();
        $isHr = AuthContext::isHr();
        $status = $assignment->status;

        return [
            'edit' => $status === AssignedFormStatus::PendingQuestionnaireEdit
                && ($assignment->supervisor_id === $staffId || $isHr),
            'fill_staff' => $status === AssignedFormStatus::PendingStaff && $assignment->staff_id === $staffId,
            'fill_supervisor' => $status === AssignedFormStatus::PendingSupervisor && $assignment->supervisor_id === $staffId,
            'results' => $status !== AssignedFormStatus::PendingStaff
                && ($assignment->staff_id === $staffId || $assignment->supervisor_id === $staffId || $isHr),
        ];
    }

    protected function authorizeViewAny(): void
    {
        abort_unless(AuthContext::canAny([
            'view_any_appraisal::form::assigned::to::staff',
            'view_appraisal::form::assigned::to::staff',
        ]), 403);
    }

    protected function authorizeAssign(): void
    {
        abort_unless(AuthContext::can('assign_appraisal_appraisal::form::assigned::to::staff'), 403);
    }

    protected function authorizeView(AppraisalFormAssignedToStaff $assignment): void
    {
        $staffId = AuthContext::staffId();
        if (AuthContext::can('view_all_appraisal::form::assigned::to::staff')) {
            return;
        }

        abort_unless(
            $assignment->staff_id === $staffId || $assignment->supervisor_id === $staffId,
            403
        );
    }

    protected function authorizeResults(AppraisalFormAssignedToStaff $assignment): void
    {
        $this->authorizeView($assignment);
    }
}
