<?php

namespace App\Http\Controllers;

use App\Enum\AppraisalFormLevel;
use App\Enum\AppraisalFormType;
use App\Models\AppraisalForm;
use App\Services\AppraisalFormStructureService;
use App\Services\AppraisalLibraryService;
use App\Support\AuthContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppraisalFormController extends Controller
{
    public function __construct(
        protected AppraisalFormStructureService $structureService,
        protected AppraisalLibraryService $libraryService,
    ) {}

    public function index(): Response
    {
        abort_unless(AuthContext::can('view_any_appraisal::form'), 403);

        $forms = AppraisalForm::latest()->paginate(15)->through(function ($form) {
            $stats = $this->structureService->stats($form);

            return [
                'id' => $form->id,
                'name' => $form->name,
                'type' => $form->type instanceof AppraisalFormType ? $form->type->getLabel() : $form->type,
                'level' => $form->level instanceof AppraisalFormLevel ? $form->level->getLabel() : $form->level,
                'is_active' => $form->is_active,
                'description' => $form->description,
                'categories_count' => $stats['categories'],
                'indicators_count' => $stats['indicators'],
            ];
        });

        return Inertia::render('AppraisalForms/Index', ['forms' => $forms]);
    }

    public function create(): Response
    {
        abort_unless(AuthContext::can('create_appraisal::form'), 403);

        return Inertia::render('AppraisalForms/Builder', [
            'form' => null,
            'library' => $this->libraryService->tree(),
            'types' => collect(AppraisalFormType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->getLabel()]),
            'levels' => collect(AppraisalFormLevel::cases())->mapWithKeys(fn ($l) => [$l->value => $l->getLabel()]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(AuthContext::can('create_appraisal::form'), 403);

        $data = $this->validateStructure($request);
        $form = $this->structureService->save(null, $data);

        return redirect()->route('appraisal-forms.edit', $form)
            ->with('success', 'Appraisal form created.');
    }

    public function edit(AppraisalForm $appraisalForm): Response
    {
        abort_unless(AuthContext::can('update_appraisal::form'), 403);

        return Inertia::render('AppraisalForms/Builder', [
            'form' => $this->structureService->load($appraisalForm),
            'library' => $this->libraryService->tree(),
            'types' => collect(AppraisalFormType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->getLabel()]),
            'levels' => collect(AppraisalFormLevel::cases())->mapWithKeys(fn ($l) => [$l->value => $l->getLabel()]),
        ]);
    }

    public function update(Request $request, AppraisalForm $appraisalForm): RedirectResponse
    {
        abort_unless(AuthContext::can('update_appraisal::form'), 403);

        $data = $this->validateStructure($request);
        $this->structureService->save($appraisalForm, $data);

        return redirect()->route('appraisal-forms.edit', $appraisalForm)
            ->with('success', 'Appraisal form saved.');
    }

    public function destroy(AppraisalForm $appraisalForm): RedirectResponse
    {
        abort_unless(AuthContext::can('delete_appraisal::form'), 403);

        $appraisalForm->delete();

        return redirect()->route('appraisal-forms.index')
            ->with('success', 'Appraisal form deleted.');
    }

    protected function validateStructure(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'level' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'categories' => ['nullable', 'array'],
            'categories.*.id' => ['nullable', 'integer', 'exists:appraisal_form_categories,id'],
            'categories.*.name' => ['required', 'string', 'max:255'],
            'categories.*.behaviors' => ['nullable', 'array'],
            'categories.*.behaviors.*.id' => ['nullable', 'integer', 'exists:appraisal_form_key_behaviors,id'],
            'categories.*.behaviors.*.name' => ['required', 'string', 'max:255'],
            'categories.*.behaviors.*.indicators' => ['nullable', 'array'],
            'categories.*.behaviors.*.indicators.*.id' => ['nullable', 'integer', 'exists:appraisal_form_questions,id'],
            'categories.*.behaviors.*.indicators.*.behavioral_indicators' => ['required', 'string'],
            'categories.*.behaviors.*.indicators.*.dhivehi_behavioral_indicators' => ['nullable', 'string'],
        ]);
    }
}
