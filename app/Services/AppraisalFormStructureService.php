<?php

namespace App\Services;

use App\Enum\AppraisalFormCategoryType;
use App\Enum\AppraisalFormLevel;
use App\Models\AppraisalForm;
use App\Models\AppraisalFormCategory;
use App\Models\AppraisalFormEntries;
use App\Models\AppraisalFormKeyBehavior;
use App\Models\AppraisalFormQuestions;
use App\Models\HodFormAssigneeEntry;
use App\Models\HodFormEntries;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppraisalFormStructureService
{
    public function load(AppraisalForm $form): array
    {
        $form->load([
            'appraisalFormCategories' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'appraisalFormCategories.appraisalFormKeyBehaviors' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'appraisalFormCategories.appraisalFormKeyBehaviors.appraisalFormQuestions' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
        ]);

        return [
            'id' => $form->id,
            'name' => $form->name,
            'type' => $form->type?->value ?? $form->type,
            'level' => $form->level?->value ?? $form->level,
            'description' => $form->description,
            'is_active' => $form->is_active,
            'categories' => $form->appraisalFormCategories->map(fn (AppraisalFormCategory $category) => [
                'id' => $category->id,
                'library_id' => $category->appraisal_form_id === null ? $category->id : null,
                'linked' => $category->appraisal_form_id === null,
                'name' => $category->name,
                'behaviors' => $category->appraisalFormKeyBehaviors->map(fn (AppraisalFormKeyBehavior $behavior) => [
                    'id' => $behavior->id,
                    'name' => $behavior->name,
                    'indicators' => $behavior->appraisalFormQuestions->map(fn (AppraisalFormQuestions $question) => [
                        'id' => $question->id,
                        'behavioral_indicators' => $question->behavioral_indicators,
                        'dhivehi_behavioral_indicators' => $question->dhivehi_behavioral_indicators,
                        'locked' => $this->questionHasEntries($question->id),
                    ])->values()->all(),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    public function save(?AppraisalForm $form, array $data): AppraisalForm
    {
        return DB::transaction(function () use ($form, $data) {
            if ($form) {
                $form->update([
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'level' => $data['level'],
                    'description' => $data['description'] ?? null,
                    'is_active' => $data['is_active'] ?? true,
                ]);
            } else {
                $form = AppraisalForm::create([
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'level' => $data['level'],
                    'description' => $data['description'] ?? null,
                    'is_active' => $data['is_active'] ?? true,
                ]);
            }

            $categoryIds = [];
            $categoryType = $this->categoryTypeForLevel($data['level']);

            foreach ($data['categories'] ?? [] as $categoryIndex => $categoryData) {
                $category = $this->resolveCategory($form, $categoryData, $categoryIndex, $categoryType);
                $categoryIds[] = $category->id;

                if ($category->appraisal_form_id !== null) {
                    $behaviorIds = [];
                    foreach ($categoryData['behaviors'] ?? [] as $behaviorIndex => $behaviorData) {
                        $behavior = $this->resolveBehavior($category, $behaviorData, $behaviorIndex);
                        $behaviorIds[] = $behavior->id;

                        $questionIds = [];
                        foreach ($behaviorData['indicators'] ?? [] as $indicatorIndex => $indicatorData) {
                            $question = $this->resolveQuestion($behavior, $indicatorData, $indicatorIndex);
                            $questionIds[] = $question->id;
                        }

                        $this->deleteRemovedQuestions($behavior, $questionIds);
                    }

                    $this->deleteRemovedBehaviors($category, $behaviorIds);
                }
            }

            $this->deleteRemovedCategories($form, $categoryIds);
            $form->appraisalFormCategories()->sync($categoryIds);

            return $form->fresh();
        });
    }

    protected function resolveCategory(AppraisalForm $form, array $data, int $sortOrder, ?AppraisalFormCategoryType $type): AppraisalFormCategory
    {
        $category = null;

        if (! empty($data['id'])) {
            $category = AppraisalFormCategory::find($data['id']);

            if ($category && $category->appraisal_form_id === null) {
                $category->name = $data['name'];
                $category->sort_order = $sortOrder;
                $category->save();

                $this->syncCategoryChildren($category, $data['behaviors'] ?? []);

                return $category;
            }

            if ($category && $this->categoryIsShared($category, $form)) {
                $category = $this->cloneCategoryTree($category, $form, $type);
            }
        }

        if (! $category) {
            $category = new AppraisalFormCategory;
            $category->appraisal_form_id = $form->id;
            $category->type = $type;
        }

        $category->name = $data['name'];
        $category->sort_order = $sortOrder;
        $category->appraisal_form_id = $category->appraisal_form_id ?? $form->id;
        $category->type = $category->type ?? $type;
        $category->save();

        return $category;
    }

    protected function syncCategoryChildren(AppraisalFormCategory $category, array $behaviors): void
    {
        $behaviorIds = [];

        foreach ($behaviors as $behaviorIndex => $behaviorData) {
            $behavior = $this->resolveBehavior($category, $behaviorData, $behaviorIndex);
            $behaviorIds[] = $behavior->id;

            $questionIds = [];
            foreach ($behaviorData['indicators'] ?? [] as $indicatorIndex => $indicatorData) {
                $question = $this->resolveQuestion($behavior, $indicatorData, $indicatorIndex);
                $questionIds[] = $question->id;
            }

            $this->deleteRemovedQuestions($behavior, $questionIds);
        }

        $this->deleteRemovedBehaviors($category, $behaviorIds);
    }

    protected function resolveBehavior(AppraisalFormCategory $category, array $data, int $sortOrder): AppraisalFormKeyBehavior
    {
        $behavior = ! empty($data['id'])
            ? AppraisalFormKeyBehavior::where('appraisal_form_category_id', $category->id)->find($data['id'])
            : null;

        if (! $behavior) {
            $behavior = new AppraisalFormKeyBehavior;
            $behavior->appraisal_form_category_id = $category->id;
        }

        $behavior->name = $data['name'];
        $behavior->sort_order = $sortOrder;
        $behavior->save();

        return $behavior;
    }

    protected function resolveQuestion(AppraisalFormKeyBehavior $behavior, array $data, int $sortOrder): AppraisalFormQuestions
    {
        $question = ! empty($data['id'])
            ? AppraisalFormQuestions::where('appraisal_form_key_behavior_id', $behavior->id)->find($data['id'])
            : null;

        if (! $question) {
            $question = new AppraisalFormQuestions;
            $question->appraisal_form_key_behavior_id = $behavior->id;
        }

        $question->behavioral_indicators = $data['behavioral_indicators'];
        $question->dhivehi_behavioral_indicators = $data['dhivehi_behavioral_indicators'] ?? null;
        $question->sort_order = $sortOrder;
        $question->save();

        return $question;
    }

    protected function deleteRemovedQuestions(AppraisalFormKeyBehavior $behavior, array $keepIds): void
    {
        $behavior->appraisalFormQuestions()
            ->whereNotIn('id', $keepIds)
            ->get()
            ->each(function (AppraisalFormQuestions $question) {
                if ($this->questionHasEntries($question->id)) {
                    throw ValidationException::withMessages([
                        'categories' => "Cannot remove indicator \"{$question->behavioral_indicators}\" because it is used in existing appraisals.",
                    ]);
                }
                $question->delete();
            });
    }

    protected function deleteRemovedBehaviors(AppraisalFormCategory $category, array $keepIds): void
    {
        $category->appraisalFormKeyBehaviors()
            ->whereNotIn('id', $keepIds)
            ->with('appraisalFormQuestions')
            ->get()
            ->each(function (AppraisalFormKeyBehavior $behavior) {
                foreach ($behavior->appraisalFormQuestions as $question) {
                    if ($this->questionHasEntries($question->id)) {
                        throw ValidationException::withMessages([
                            'categories' => "Cannot remove behavior \"{$behavior->name}\" because it is used in existing appraisals.",
                        ]);
                    }
                }
                $behavior->appraisalFormQuestions()->delete();
                $behavior->delete();
            });
    }

    protected function deleteRemovedCategories(AppraisalForm $form, array $keepIds): void
    {
        $form->appraisalFormCategories()
            ->whereNotIn('appraisal_form_categories.id', $keepIds)
            ->with('appraisalFormKeyBehaviors.appraisalFormQuestions')
            ->get()
            ->each(function (AppraisalFormCategory $category) use ($form) {
                if ($category->appraisal_form_id === null) {
                    return;
                }

                if ($category->appraisal_form_id !== $form->id && $this->categoryIsShared($category, $form)) {
                    return;
                }

                foreach ($category->appraisalFormKeyBehaviors as $behavior) {
                    foreach ($behavior->appraisalFormQuestions as $question) {
                        if ($this->questionHasEntries($question->id)) {
                            throw ValidationException::withMessages([
                                'categories' => "Cannot remove category \"{$category->name}\" because it is used in existing appraisals.",
                            ]);
                        }
                    }
                }

                if ($category->appraisal_form_id === $form->id) {
                    $category->appraisalFormKeyBehaviors->each(function (AppraisalFormKeyBehavior $behavior) {
                        $behavior->appraisalFormQuestions()->delete();
                        $behavior->delete();
                    });
                    $category->delete();
                }
            });
    }

    protected function categoryIsShared(AppraisalFormCategory $category, AppraisalForm $form): bool
    {
        return $category->appraisalForms()->where('appraisal_form_id', '!=', $form->id)->exists();
    }

    protected function cloneCategoryTree(AppraisalFormCategory $source, AppraisalForm $form, ?AppraisalFormCategoryType $type): AppraisalFormCategory
    {
        $source->load('appraisalFormKeyBehaviors.appraisalFormQuestions');

        $category = $source->replicate();
        $category->appraisal_form_id = $form->id;
        $category->type = $type ?? $category->type;
        $category->save();

        foreach ($source->appraisalFormKeyBehaviors as $behavior) {
            $newBehavior = $behavior->replicate();
            $newBehavior->appraisal_form_category_id = $category->id;
            $newBehavior->save();

            foreach ($behavior->appraisalFormQuestions as $question) {
                $newQuestion = $question->replicate();
                $newQuestion->appraisal_form_key_behavior_id = $newBehavior->id;
                $newQuestion->save();
            }
        }

        return $category;
    }

    protected function categoryTypeForLevel(string $level): ?AppraisalFormCategoryType
    {
        $enum = AppraisalFormLevel::tryFrom($level);

        return match ($enum) {
            AppraisalFormLevel::Level1 => AppraisalFormCategoryType::FormLevel1,
            AppraisalFormLevel::Level2 => AppraisalFormCategoryType::FormLevel2,
            AppraisalFormLevel::Level3 => AppraisalFormCategoryType::FormLevel3,
            AppraisalFormLevel::Probationary => AppraisalFormCategoryType::FormProbationary,
            default => null,
        };
    }

    protected function questionHasEntries(int $questionId): bool
    {
        return $this->questionIsLocked($questionId);
    }

    public function questionIsLocked(int $questionId): bool
    {
        return AppraisalFormEntries::where('question_id', $questionId)->exists()
            || HodFormEntries::where('question_id', $questionId)->exists()
            || HodFormAssigneeEntry::where('question_id', $questionId)->exists();
    }

    public function stats(AppraisalForm $form): array
    {
        $form->load('appraisalFormCategories.appraisalFormKeyBehaviors.appraisalFormQuestions');

        $categories = $form->appraisalFormCategories->count();
        $behaviors = 0;
        $indicators = 0;

        foreach ($form->appraisalFormCategories as $category) {
            $behaviors += $category->appraisalFormKeyBehaviors->count();
            foreach ($category->appraisalFormKeyBehaviors as $behavior) {
                $indicators += $behavior->appraisalFormQuestions->count();
            }
        }

        return compact('categories', 'behaviors', 'indicators');
    }
}
