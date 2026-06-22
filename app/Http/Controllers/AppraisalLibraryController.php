<?php

namespace App\Http\Controllers;

use App\Enum\AppraisalFormLevel;
use App\Enum\AppraisalFormType;
use App\Services\AppraisalLibraryService;
use App\Support\AuthContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppraisalLibraryController extends Controller
{
    public function __construct(
        protected AppraisalLibraryService $libraryService,
    ) {}

    public function index(): Response
    {
        abort_unless(AuthContext::can('view_any_appraisal::form'), 403);

        return Inertia::render('AppraisalForms/Builder', [
            'libraryOnly' => true,
            'form' => $this->libraryService->load(),
            'library' => $this->libraryService->tree(),
            'types' => collect(AppraisalFormType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->getLabel()]),
            'levels' => collect(AppraisalFormLevel::cases())->mapWithKeys(fn ($l) => [$l->value => $l->getLabel()]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(AuthContext::can('update_appraisal::form'), 403);

        $data = $request->validate([
            'categories' => ['nullable', 'array'],
            'categories.*.id' => ['nullable', 'integer', 'exists:appraisal_form_categories,id'],
            'categories.*.name' => ['required', 'string', 'max:255'],
            'categories.*.level' => ['required', 'string'],
            'categories.*.form_type' => ['required', 'string'],
            'categories.*.behaviors' => ['nullable', 'array'],
            'categories.*.behaviors.*.id' => ['nullable', 'integer', 'exists:appraisal_form_key_behaviors,id'],
            'categories.*.behaviors.*.name' => ['required', 'string', 'max:255'],
            'categories.*.behaviors.*.indicators' => ['nullable', 'array'],
            'categories.*.behaviors.*.indicators.*.id' => ['nullable', 'integer', 'exists:appraisal_form_questions,id'],
            'categories.*.behaviors.*.indicators.*.behavioral_indicators' => ['required', 'string'],
            'categories.*.behaviors.*.indicators.*.dhivehi_behavioral_indicators' => ['nullable', 'string'],
        ]);

        $this->libraryService->save($data);

        return redirect()->route('appraisal-library.index')
            ->with('success', 'Library saved.');
    }
}
