<?php

namespace App\Services;

use App\Enum\AppraisalFormCategoryType;
use App\Enum\AppraisalFormLevel;
use App\Enum\AppraisalFormType;
use App\Models\AppraisalFormCategory;
use App\Models\AppraisalFormKeyBehavior;
use App\Models\AppraisalFormQuestions;
use Illuminate\Support\Facades\DB;

class AppraisalLibraryService
{
    public function __construct(
        protected AppraisalFormStructureService $structureService,
    ) {}

    public function tree(?string $formLevel = null, ?string $formType = null): array
    {
        $query = AppraisalFormCategory::query()
            ->whereNull('appraisal_form_id')
            ->with([
                'appraisalFormKeyBehaviors' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
                'appraisalFormKeyBehaviors.appraisalFormQuestions' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($formLevel) {
            $query->where('type', self::levelToCategoryType($formLevel)->value);
        }

        if ($formType) {
            $query->where('form_type', $formType);
        }

        return [
            'categories' => $query->get()
                ->map(fn (AppraisalFormCategory $category) => $this->mapCategory($category))
                ->values()
                ->all(),
        ];
    }

    public function load(): array
    {
        return [
            'categories' => collect($this->tree()['categories'])->map(function (array $category) {
                unset($category['linked']);

                return $category;
            })->values()->all(),
        ];
    }

    public function save(array $data): void
    {
        DB::transaction(function () use ($data) {
            $keepCategoryIds = [];

            foreach ($data['categories'] ?? [] as $categoryIndex => $categoryData) {
                $category = ! empty($categoryData['id'])
                    ? AppraisalFormCategory::whereNull('appraisal_form_id')->findOrFail($categoryData['id'])
                    : new AppraisalFormCategory(['appraisal_form_id' => null]);

                $category->name = $categoryData['name'];
                $category->type = self::levelToCategoryType($categoryData['level'] ?? 'level_1');
                $category->form_type = $categoryData['form_type'] ?? AppraisalFormType::MidYear->value;
                $category->sort_order = $categoryIndex;
                $category->appraisal_form_id = null;
                $category->save();
                $keepCategoryIds[] = $category->id;

                $keepBehaviorIds = [];
                foreach ($categoryData['behaviors'] ?? [] as $behaviorIndex => $behaviorData) {
                    $behavior = $this->resolveLibraryBehavior($category, $behaviorData, $behaviorIndex);
                    $keepBehaviorIds[] = $behavior->id;

                    $keepQuestionIds = [];
                    foreach ($behaviorData['indicators'] ?? [] as $indicatorIndex => $indicatorData) {
                        $question = $this->resolveLibraryQuestion($behavior, $indicatorData, $indicatorIndex);
                        $keepQuestionIds[] = $question->id;
                    }

                    $behavior->appraisalFormQuestions()
                        ->whereNotIn('id', $keepQuestionIds)
                        ->delete();
                }

                $category->appraisalFormKeyBehaviors()
                    ->whereNotIn('id', $keepBehaviorIds)
                    ->get()
                    ->each(function (AppraisalFormKeyBehavior $behavior) {
                        $behavior->appraisalFormQuestions()->delete();
                        $behavior->delete();
                    });
            }

            AppraisalFormCategory::query()
                ->whereNull('appraisal_form_id')
                ->whereNotIn('id', $keepCategoryIds)
                ->get()
                ->each(function (AppraisalFormCategory $category) {
                    $category->appraisalFormKeyBehaviors->each(function (AppraisalFormKeyBehavior $behavior) {
                        $behavior->appraisalFormQuestions()->delete();
                        $behavior->delete();
                    });
                    $category->appraisalForms()->detach();
                    $category->delete();
                });
        });
    }

    public static function levelToCategoryType(string $level): AppraisalFormCategoryType
    {
        return match (AppraisalFormLevel::tryFrom($level)) {
            AppraisalFormLevel::Level2 => AppraisalFormCategoryType::FormLevel2,
            AppraisalFormLevel::Level3 => AppraisalFormCategoryType::FormLevel3,
            AppraisalFormLevel::Probationary => AppraisalFormCategoryType::FormProbationary,
            default => AppraisalFormCategoryType::FormLevel1,
        };
    }

    public static function categoryTypeToLevel(AppraisalFormCategoryType|string|null $type): string
    {
        $enum = $type instanceof AppraisalFormCategoryType
            ? $type
            : AppraisalFormCategoryType::tryFrom((string) $type);

        return match ($enum) {
            AppraisalFormCategoryType::FormLevel2 => AppraisalFormLevel::Level2->value,
            AppraisalFormCategoryType::FormLevel3 => AppraisalFormLevel::Level3->value,
            AppraisalFormCategoryType::FormProbationary => AppraisalFormLevel::Probationary->value,
            default => AppraisalFormLevel::Level1->value,
        };
    }

    protected function resolveLibraryBehavior(AppraisalFormCategory $category, array $data, int $sortOrder): AppraisalFormKeyBehavior
    {
        $behavior = ! empty($data['id'])
            ? AppraisalFormKeyBehavior::where('appraisal_form_category_id', $category->id)->findOrFail($data['id'])
            : new AppraisalFormKeyBehavior(['appraisal_form_category_id' => $category->id]);

        $behavior->name = $data['name'];
        $behavior->sort_order = $sortOrder;
        $behavior->save();

        return $behavior;
    }

    protected function resolveLibraryQuestion(AppraisalFormKeyBehavior $behavior, array $data, int $sortOrder): AppraisalFormQuestions
    {
        $question = ! empty($data['id'])
            ? AppraisalFormQuestions::where('appraisal_form_key_behavior_id', $behavior->id)->findOrFail($data['id'])
            : new AppraisalFormQuestions(['appraisal_form_key_behavior_id' => $behavior->id]);

        $question->behavioral_indicators = $data['behavioral_indicators'];
        $question->dhivehi_behavioral_indicators = $data['dhivehi_behavioral_indicators'] ?? null;
        $question->sort_order = $sortOrder;
        $question->save();

        return $question;
    }

    protected function mapCategory(AppraisalFormCategory $category, bool $copy = false): array
    {
        return [
            'id' => $copy ? null : $category->id,
            'library_id' => $category->id,
            'name' => $category->name,
            'level' => self::categoryTypeToLevel($category->type),
            'form_type' => $category->form_type?->value ?? $category->form_type,
            'linked' => ! $copy,
            'behaviors' => $category->appraisalFormKeyBehaviors->map(fn (AppraisalFormKeyBehavior $b) => $this->mapBehavior($b, $copy))->values()->all(),
        ];
    }

    protected function mapBehavior(AppraisalFormKeyBehavior $behavior, bool $copy = false): array
    {
        return [
            'id' => $copy ? null : $behavior->id,
            'library_id' => $behavior->id,
            'name' => $behavior->name,
            'indicators' => $behavior->appraisalFormQuestions->map(fn (AppraisalFormQuestions $q) => $this->mapIndicator($q, $copy))->values()->all(),
        ];
    }

    protected function mapIndicator(AppraisalFormQuestions $question, bool $copy = false): array
    {
        return [
            'id' => $copy ? null : $question->id,
            'library_id' => $question->id,
            'behavioral_indicators' => $question->behavioral_indicators,
            'dhivehi_behavioral_indicators' => $question->dhivehi_behavioral_indicators,
            'locked' => ! $copy && $this->structureService->questionIsLocked($question->id),
        ];
    }
}
