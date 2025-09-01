<?php

use App\Models\Staff;
use App\Services\Shortcuts;
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

Route::get('/appraisal-form-fill/{record}', function ($record) {
    $assigned = \App\Models\AppraisalFormAssignedToStaff::findOrFail($record);

    $ratingScale = [
        ['label' => '1', 'description' => "Doesn't meet requirements"],
        ['label' => '2', 'description' => "Meets some requirements"],
        ['label' => '3', 'description' => "Meets all requirements"],
        ['label' => '4', 'description' => "Exceeds some requirements, fully met others"],
        ['label' => '5', 'description' => "Exceeds all requirements"],
    ];
    $department = Shortcuts::callgetapi('/users/department', ['id' => $assigned->staff->api_id,])->json();
    $supervisor = Shortcuts::callgetapi('/users/supervisor', ['id' => $assigned->staff->api_id,])->json();
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
    return view('appraisal-form-fill', [
        'assigned' => $assigned,
        'ratingScale' => $ratingScale,
        'appraisalData' => $appraisalData,
    ]);
})->name('appraisal-form-fill');

Route::post('/appraisal-form-fill/{record}', function ($record) {
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
})->name('appraisal-form-fill.submit');

Route::get('/supervisor-appraisal-form-fill/{record}', function ($record) {
    $assigned = \App\Models\AppraisalFormAssignedToStaff::findOrFail($record);

    $ratingScale = [
        ['label' => '1', 'description' => "Doesn't meet requirements"],
        ['label' => '2', 'description' => "Meets some requirements"],
        ['label' => '3', 'description' => "Meets all requirements"],
        ['label' => '4', 'description' => "Exceeds some requirements, fully met others"],
        ['label' => '5', 'description' => "Exceeds all requirements"],
    ];
    $department = Shortcuts::callgetapi('/users/department', ['id' => $assigned->staff->api_id,])->json();
    $supervisor = Shortcuts::callgetapi('/users/supervisor', ['id' => $assigned->staff->api_id,])->json();
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
                        'selfScore' =>  $entry->staff_score,
                        'supervisorScore' =>  '',
                        'supervisorcomment' =>  '',
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
    return view('supervisor-appraisal-form-fill', [
        'assigned' => $assigned,
        'ratingScale' => $ratingScale,
        'appraisalData' => $appraisalData,
    ]);
})->name('supervisor-appraisal-form-fill');

Route::post('/supervisor-appraisal-form-fill/{record}', function ($record) {
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
                        'selfScore' => $entry->staff_score,
                        'supervisorScore' => $entry->supervisor_score,
                        'supervisorcomment' => $entry->supervisor_comment,
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

    // Only update supervisor fields
    foreach ($answers as $categoryIndex => $behaviors) {
        foreach ($behaviors as $behaviorIndex => $indicators) {
            foreach ($indicators as $indicatorIndex => $answerArr) {
                $questionId = $appraisalData[$categoryIndex]['keyBehaviors'][$behaviorIndex]['indicators'][$indicatorIndex]['question_id'] ?? null;
                if ($questionId) {
                    \App\Models\AppraisalFormEntries::where('appraisal_assigned_to_staff_id', $assigned->id)
                        ->where('question_id', $questionId)
                        ->update([
                            'supervisor_score' => $answerArr['supervisor_score'] ?? null,
                            'supervisor_comment' => $answerArr['supervisor_comment'] ?? null,
                        ]);
                }
            }
        }
    }

    $assigned->update([
        'supervisor_comment' => $request['supervisorComments'] ?? '',
        'status' => \App\Enum\AssignedFormStatus::Complete->value,
    ]);

    return redirect()->route('filament.staff.resources.appraisal-form-assigned-to-staffs.index')
        ->with('success', 'Supervisor appraisal submitted successfully!');
})->name('supervisor-appraisal-form-fill.submit');

//HOD Forms
Route::get('/hod-appraisal-form-fill/{record}', function ($record) {
    $assigned = \App\Models\FormsAssignedToHod::findOrFail($record);

    $ratingScale = [
        ['label' => '1', 'description' => "Doesn't meet requirements"],
        ['label' => '2', 'description' => "Meets some requirements"],
        ['label' => '3', 'description' => "Meets all requirements"],
        ['label' => '4', 'description' => "Exceeds some requirements, fully met others"],
        ['label' => '5', 'description' => "Exceeds all requirements"],
    ];
    $department = Shortcuts::callgetapi('/users/department', ['id' => $assigned->staff->api_id,])->json();
    $supervisor = Shortcuts::callgetapi('/users/supervisor', ['id' => $assigned->staff->api_id,])->json();
    $assigned->staff->department = $department['name'];
    $assigned->staff->supervisor = $supervisor['name'];

    // Build appraisalData from related models
    $appraisalData4 = [];
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
                $entry = \App\Models\HodFormEntries::where('forms_assigned_to_hod_id', $assigned->id)
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
            $appraisalData4[] = $categorySection;
        }
    }
    return view('hod-appraisal-form-fill', [
        'assigned' => $assigned,
        'ratingScale' => $ratingScale,
        'appraisalData' => $appraisalData4,
    ]);
})->name('hod-appraisal-form-fill');

Route::post('/hod-appraisal-form-fill/{record}', function ($record) {
    $request = request()->all();
    $assigned3 = \App\Models\FormsAssignedToHod::findOrFail($record);
    $answers = $request['appraisalScores'] ?? [];

    // Rebuild appraisalData from relations (same as GET route)
    $appraisalData3 = [];
    foreach ($assigned3->appraisalForm->appraisalFormCategories as $category) {
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
                $entry = \App\Models\HodFormEntries::where('forms_assigned_to_hod_id', $assigned3->id)
                    ->where('question_id', $question->id)
                    ->where('hidden', false)
                    ->first();
                if ($entry) {
                    $behaviorSection['indicators'][] = [
                        'text' => $question->behavioral_indicators,
                        'self_score' => '', // Fill if needed
                        'question_id' => $question->id,
                    ];
                }
            }
            if (count($behaviorSection['indicators'])) {
                $categorySection['keyBehaviors'][] = $behaviorSection;
            }
        }
        if (count($categorySection['keyBehaviors'])) {
            $appraisalData3[] = $categorySection;
        }
    }

    // Now process answers as before
    foreach ($answers as $categoryIndex => $behaviors) {
        foreach ($behaviors as $behaviorIndex => $indicators) {
            foreach ($indicators as $indicatorIndex => $answerArr) {
                $score = $answerArr['self_score'] ?? null;
                $questionId = $appraisalData3[$categoryIndex]['keyBehaviors'][$behaviorIndex]['indicators'][$indicatorIndex]['question_id'] ?? null;
                if ($questionId) {
                    \App\Models\HodFormEntries::where('forms_assigned_to_hod_id', $assigned3->id)
                        ->where('question_id', $questionId)
                        ->update([
                            'self_score' => $score ?? null,
                            'comment' => $answerArr['supervisor_comment'] ?? null,
                        ]);
                }
            }
        }
    }

    $assigned3->update([
        'hod_comment' => $request['employeeComments'] ?? '',
        'status' => \App\Enum\HODFormassigneeStatus::PendingAssignee->value,
    ]);
    return redirect()->route('filament.staff.resources.forms-assigned-to-hods.index')
        ->with('success', 'Appraisal form submitted successfully!');
    Notification::make()
        ->title('Success')
        ->body('Appraisal form submitted successfully!')
        ->success()
        ->send();
})->name('hod-appraisal-form-fill.submit');

Route::get('/assignee-hod-appraisal-form-fill/{record}', function ($record) {
    $assigned = \App\Models\FormsAssignedToHod::findOrFail($record);

    $ratingScale = [
        ['label' => '1', 'description' => "Doesn't meet requirements"],
        ['label' => '2', 'description' => "Meets some requirements"],
        ['label' => '3', 'description' => "Meets all requirements"],
        ['label' => '4', 'description' => "Exceeds some requirements, fully met others"],
        ['label' => '5', 'description' => "Exceeds all requirements"],
    ];
    $department = Shortcuts::callgetapi('/users/department', ['id' => $assigned->staff->api_id,])->json();
    $supervisor = Shortcuts::callgetapi('/users/supervisor', ['id' => $assigned->staff->api_id,])->json();
    $assigned->staff->department = $department['name'];
    $assigned->staff->supervisor->name = $supervisor['name'];
    $assigned->staff->supervisor->designation = $supervisor['designation'];

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

            $assignee = auth('staff')->check() ? auth('staff')->user()->id : null;
//            $assignee = 8; //temp
            $assigneerecord = \App\Models\HodFormAssignee::where('forms_assigned_to_hod_id', $assigned->id)
                ->where('assignee_id', $assignee)
                ->first();
            foreach ($behavior->appraisalFormQuestions as $question) {
                $entry = $assigneerecord->hodFormAssigneeEntries()
                    ->where('question_id', $question->id)
                    ->where('hidden', false)
                    ->first();
                if ($entry) {
                    $behaviorSection['indicators'][] = [
                        'text' => $question->behavioral_indicators,
                        'selfScore' =>  $entry->staff_score,
                        'supervisorScore' =>  '',
                        'supervisorcomment' =>  '',
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
    $assigneedata = \App\Models\Staff::where('id',$assignee)->first();
    return view('assingee-hod-appraisal-form-fill', [
        'assignee' => $assigneedata,
        'assigned' => $assigned,
        'ratingScale' => $ratingScale,
        'appraisalData' => $appraisalData,
    ]);
})->name('assignee-hod-appraisal-form-fill');

Route::post('/assignee-hod-appraisal-form-fill/{record}', function ($record) {
    $request = request()->all();
    $assigned2 = \App\Models\FormsAssignedToHod::findOrFail($record);
//    dd($request);
    $assignee = Staff::where('emp_no',$request['assignee']['staffId'])->first();
    $answers = $request['appraisalScores'] ?? [];

    // Rebuild appraisalData from relations (same as GET route)
    $appraisalData2 = [];
    foreach ($assigned2->appraisalForm->appraisalFormCategories as $category) {
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
                $entry = \App\Models\AppraisalFormEntries::where('appraisal_assigned_to_staff_id', $assigned2->id)
                    ->where('question_id', $question->id)
                    ->where('hidden', false)
                    ->first();
                if ($entry) {
                    $behaviorSection['indicators'][] = [
                        'text' => $question->behavioral_indicators,
                        'selfScore' => $entry->staff_score,
                        'supervisorScore' => $entry->supervisor_score,
                        'supervisorcomment' => $entry->supervisor_comment,
                        'question_id' => $question->id,
                    ];
                }
            }
            if (count($behaviorSection['indicators'])) {
                $categorySection['keyBehaviors'][] = $behaviorSection;
            }
        }
        if (count($categorySection['keyBehaviors'])) {
            $appraisalData2[] = $categorySection;
        }
    }
    $assigneeform = \App\Models\HodFormAssignee::where('forms_assigned_to_hod_id', $assigned2->id)
        ->where('assignee_id', $assignee->id)
        ->first();
    // Only update supervisor fields
    foreach ($answers as $categoryIndex => $behaviors) {
        foreach ($behaviors as $behaviorIndex => $indicators) {
            foreach ($indicators as $indicatorIndex => $answerArr) {
                $questionId = $appraisalData2[$categoryIndex]['keyBehaviors'][$behaviorIndex]['indicators'][$indicatorIndex]['question_id'] ?? null;
                if ($questionId) {
                    \App\Models\HodFormAssigneeEntry::where('hod_form_assignee_id', $assigneeform->id)
                        ->where('question_id', $questionId)
                        ->update([
                            'score' => $answerArr['supervisor_score'] ?? null,
                            'comment' => $answerArr['supervisor_comment'] ?? null,
                        ]);
                }
            }
        }
    }

    $assigneeform->update([
        'assignee_comment' => $request['supervisorComments'] ?? '',
        'status' => \App\Enum\HODFormassigneeStatus::Completed->value,
    ]);
    if ($assigned2->hodFormAssignees->every(fn($hodFormAssignees) => $hodFormAssignees->status === \App\Enum\HODFormassigneeStatus::Completed)) {
        $assigned2->update([
            'status' => \App\Enum\HODFormassigneeStatus::Completed->value,
        ]);
    }

    return redirect()->route('filament.staff.resources.forms-assigned-to-hods.index')
        ->with('success', 'Supervisor appraisal submitted successfully!');
})->name('assignee-hod-appraisal-form-fill.submit');
