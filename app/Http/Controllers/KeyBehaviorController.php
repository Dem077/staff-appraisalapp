<?php

namespace App\Http\Controllers;

use App\Models\AppraisalFormCategory;
use App\Models\AppraisalFormKeyBehavior;
use App\Models\AppraisalFormQuestions;
use App\Support\AuthContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KeyBehaviorController extends Controller
{
    public function index(): Response
    {
        abort_unless(AuthContext::can('view_any_appraisal::form::key::behavior'), 403);

        $behaviors = AppraisalFormKeyBehavior::with(['appraisalFormCategory', 'appraisalFormQuestions'])
            ->latest()
            ->paginate(15)
            ->through(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'category' => $b->appraisalFormCategory?->name,
                'question_count' => $b->appraisalFormQuestions->count(),
            ]);

        return Inertia::render('KeyBehaviors/Index', ['behaviors' => $behaviors]);
    }

    public function create(): Response
    {
        abort_unless(AuthContext::can('create_appraisal::form::key::behavior'), 403);

        return Inertia::render('KeyBehaviors/Form', [
            'behavior' => null,
            'categories' => AppraisalFormCategory::pluck('name', 'id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(AuthContext::can('create_appraisal::form::key::behavior'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'appraisal_form_category_id' => ['required', 'exists:appraisal_form_categories,id'],
            'questions' => ['nullable', 'array'],
            'questions.*.behavioral_indicators' => ['required', 'string'],
            'questions.*.dhivehi_behavioral_indicators' => ['nullable', 'string'],
        ]);

        $behavior = AppraisalFormKeyBehavior::create([
            'name' => $data['name'],
            'appraisal_form_category_id' => $data['appraisal_form_category_id'],
        ]);

        foreach ($data['questions'] ?? [] as $question) {
            $behavior->appraisalFormQuestions()->create($question);
        }

        return redirect()->route('key-behaviors.index')
            ->with('success', 'Key behavior created.');
    }

    public function edit(AppraisalFormKeyBehavior $keyBehavior): Response
    {
        abort_unless(AuthContext::can('update_appraisal::form::key::behavior'), 403);

        $keyBehavior->load('appraisalFormQuestions');

        return Inertia::render('KeyBehaviors/Form', [
            'behavior' => [
                'id' => $keyBehavior->id,
                'name' => $keyBehavior->name,
                'appraisal_form_category_id' => $keyBehavior->appraisal_form_category_id,
                'questions' => $keyBehavior->appraisalFormQuestions->map(fn ($q) => [
                    'id' => $q->id,
                    'behavioral_indicators' => $q->behavioral_indicators,
                    'dhivehi_behavioral_indicators' => $q->dhivehi_behavioral_indicators,
                ]),
            ],
            'categories' => AppraisalFormCategory::pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, AppraisalFormKeyBehavior $keyBehavior): RedirectResponse
    {
        abort_unless(AuthContext::can('update_appraisal::form::key::behavior'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'appraisal_form_category_id' => ['required', 'exists:appraisal_form_categories,id'],
            'questions' => ['nullable', 'array'],
            'questions.*.id' => ['nullable', 'exists:appraisal_form_questions,id'],
            'questions.*.behavioral_indicators' => ['required', 'string'],
            'questions.*.dhivehi_behavioral_indicators' => ['nullable', 'string'],
        ]);

        $keyBehavior->update([
            'name' => $data['name'],
            'appraisal_form_category_id' => $data['appraisal_form_category_id'],
        ]);

        $existingIds = [];
        foreach ($data['questions'] ?? [] as $questionData) {
            if (! empty($questionData['id'])) {
                AppraisalFormQuestions::where('id', $questionData['id'])->update([
                    'behavioral_indicators' => $questionData['behavioral_indicators'],
                    'dhivehi_behavioral_indicators' => $questionData['dhivehi_behavioral_indicators'] ?? null,
                ]);
                $existingIds[] = $questionData['id'];
            } else {
                $new = $keyBehavior->appraisalFormQuestions()->create([
                    'behavioral_indicators' => $questionData['behavioral_indicators'],
                    'dhivehi_behavioral_indicators' => $questionData['dhivehi_behavioral_indicators'] ?? null,
                ]);
                $existingIds[] = $new->id;
            }
        }

        $keyBehavior->appraisalFormQuestions()->whereNotIn('id', $existingIds)->delete();

        return redirect()->route('key-behaviors.index')
            ->with('success', 'Key behavior updated.');
    }
}
