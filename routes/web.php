<?php

use App\Services\ShortCuts;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

/* NOTE: Do Not Remove
/ Livewire asset handling if using sub folder in domain
*/
Livewire::setUpdateRoute(function ($handle) {
    return Route::post(config('app.asset_prefix') . '/livewire/update', $handle);
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get(config('app.asset_prefix') . '/livewire/livewire.js', $handle);
});
/*
/ END
*/
Route::get('/', function () {
    return view('welcome');
});

Route::get('/apraisal-form-fill/{record}', function ($record) {
    $assigned = \App\Models\AppraisalFormAssignedToStaff::findOrFail($record);

    $ratingScale = [
        ['label' => '1', 'description' => "Doesn't meet requirements"],
        ['label' => '2', 'description' => "Meets some requirements"],
        ['label' => '3', 'description' => "Meets all requirements"],
        ['label' => '4', 'description' => "Exceeds some requirements, fully met others"],
        ['label' => '5', 'description' => "Exceeds all requirements"],
    ];
    $department = ShortCuts::callgetapi('/users/department', ['id' => $assigned->staff->api_id,])->json();
    $supervisor = ShortCuts::callgetapi('/users/supervisor', ['id' => $assigned->staff->api_id,])->json();
    $assigned->staff->department = $department['name'];
    $assigned->staff->supervisor = $supervisor['name'];

    // Build appraisalData from related models
    $appraisalData = [];
    foreach ($assigned->appraisalForm->appraisalFormCategories as $category) {
        $categorySection = [
            'name' => $category->name,
            'keyBehaviors' => [],
        ];
        foreach ($category->appraisalFormKeyBehaviors as $behavior) {
            $behaviorSection = [
                'name' => $behavior->name,
                'indicators' => [],
            ];
            foreach ($behavior->appraisalFormQuestions as $question) {
                $entry = \App\Models\AppraisalFormEntries::where('appraisal_assigned_to_staff_id', $assigned->id)
                    ->where('question_id', $question->id)
                    ->where('hidden', false)
                    ->first();
                if ($entry) {
                    $behaviorSection['indicators'][] = [
                        'text' => $question->behavioral_indicators,
                        'selfScore' => '', // Fill if needed
                        'question_id' => $question->id,
                    ];
                }
            }
            if (count($behaviorSection['indicators'])) {
                $categorySection['keyBehaviors'][] = $behaviorSection;
            }
        }
        // dd($categorySection);
        if (count($categorySection['keyBehaviors'])) {
            $appraisalData[] = $categorySection;
        }
    }
    return view('apraisal-form-fill', [
        'assigned' => $assigned,
        'ratingScale' => $ratingScale,
        'appraisalData' => $appraisalData,
    ]);
})->name('apraisal-form-fill');

Route::post('/apraisal-form-fill/{record}', function ($record) {
    $request = request()->all();
    $assigned = \App\Models\AppraisalFormAssignedToStaff::findOrFail($record);
    $answers = $request['appraisalScores'] ?? [];

    // Rebuild appraisalData from relations (same as GET route)
    $appraisalData = [];
    foreach ($assigned->appraisalForm->appraisalFormCategories as $category) {
        $categorySection = [
            'name' => $category->name,
            'keyBehaviors' => [],
        ];
        foreach ($category->appraisalFormKeyBehaviors as $behavior) {
            $behaviorSection = [
                'name' => $behavior->name,
                'indicators' => [],
            ];
            foreach ($behavior->appraisalFormQuestions as $question) {
                $entry = \App\Models\AppraisalFormEntries::where('appraisal_assigned_to_staff_id', $assigned->id)
                    ->where('question_id', $question->id)
                    ->where('hidden', false)
                    ->first();
                if ($entry) {
                    $behaviorSection['indicators'][] = [
                        'text' => $question->behavioral_indicators,
                        'selfScore' => '', // Fill if needed
                        'question_id' => $question->id,
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

    // Now process answers as before
    foreach ($answers as $categoryIndex => $behaviors) {
        foreach ($behaviors as $behaviorIndex => $indicators) {
            foreach ($indicators as $indicatorIndex => $answerArr) {
                $score = $answerArr['self_score'] ?? null;
                $questionId = $appraisalData[$categoryIndex]['keyBehaviors'][$behaviorIndex]['indicators'][$indicatorIndex]['question_id'] ?? null;
                if ($questionId && $score !== null) {
                    \App\Models\AppraisalFormEntries::updateOrCreate(
                        [
                            'appraisal_assigned_to_staff_id' => $assigned->id,
                            'question_id' => $questionId,
                        ],
                        [
                            'staff_score' => $score,
                        ]
                    );
                }
            }
        }
    }

    $assigned->update([
        'staff_comment' => $request['employeeComments'] ?? '',
        'status' => App\Enum\AssignedFormStatus::PendingSupervisor->value,
    ]);
    return redirect()->route('filament.staff.resources.appraisal-form-assigned-to-staffs.index')
        ->with('success', 'Appraisal form submitted successfully!');
        Notification::make()
            ->title('Success')
            ->body('Appraisal form submitted successfully!')
            ->success()
            ->send();
})->name('apraisal-form-fill.submit');