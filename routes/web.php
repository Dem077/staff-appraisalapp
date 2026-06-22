<?php

use App\Http\Controllers\AppraisalAssignmentController;
use App\Http\Controllers\AppraisalFormController;
use App\Http\Controllers\AppraisalLibraryController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HodAssignmentController;
use App\Http\Controllers\KeyBehaviorController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StaffMemberController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth.app')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/assignments/resolve-supervisor', [AppraisalAssignmentController::class, 'resolveSupervisor'])
        ->name('assignments.resolve-supervisor');

    Route::resource('assignments', AppraisalAssignmentController::class)->only(['index', 'create', 'store', 'edit', 'destroy']);
    Route::post('/assignments/{assignment}/send-to-staff', [AppraisalAssignmentController::class, 'sendToStaff'])->name('assignments.send-to-staff');
    Route::post('/assignments/{assignment}/sync-entries', [AppraisalAssignmentController::class, 'syncEntries'])->name('assignments.sync-entries');
    Route::put('/assignments/{assignment}/entries', [AppraisalAssignmentController::class, 'updateEntries'])->name('assignments.update-entries');
    Route::get('/assignments/{assignment}/fill-staff', [AppraisalAssignmentController::class, 'fillStaff'])->name('assignments.fill-staff');
    Route::post('/assignments/{assignment}/fill-staff', [AppraisalAssignmentController::class, 'submitStaff'])->name('assignments.submit-staff');
    Route::get('/assignments/{assignment}/fill-supervisor', [AppraisalAssignmentController::class, 'fillSupervisor'])->name('assignments.fill-supervisor');
    Route::post('/assignments/{assignment}/fill-supervisor', [AppraisalAssignmentController::class, 'submitSupervisor'])->name('assignments.submit-supervisor');
    Route::get('/assignments/{assignment}/results', [AppraisalAssignmentController::class, 'results'])->name('assignments.results');
    Route::post('/assignments/{assignment}/hr-comment', [AppraisalAssignmentController::class, 'hrComment'])->name('assignments.hr-comment');
    Route::get('/assignments/{assignment}/pdf', [AppraisalAssignmentController::class, 'pdf'])->name('assignments.pdf');

    Route::get('/hod-assignments', [HodAssignmentController::class, 'index'])->name('hod-assignments.index');
    Route::get('/hod-assignments/create', [HodAssignmentController::class, 'create'])->name('hod-assignments.create');
    Route::post('/hod-assignments', [HodAssignmentController::class, 'store'])->name('hod-assignments.store');
    Route::get('/hod-assignments/{hodAssignment}/fill-hod', [HodAssignmentController::class, 'fillHod'])->name('hod-assignments.fill-hod');
    Route::post('/hod-assignments/{hodAssignment}/fill-hod', [HodAssignmentController::class, 'submitHod'])->name('hod-assignments.submit-hod');
    Route::get('/hod-assignments/{hodAssignment}/results', [HodAssignmentController::class, 'results'])->name('hod-assignments.results');
    Route::post('/hod-assignments/{hodAssignment}/hr-comment', [HodAssignmentController::class, 'hrComment'])->name('hod-assignments.hr-comment');
    Route::get('/hod-assignees/{assignee}/fill', [HodAssignmentController::class, 'fillAssignee'])->name('hod-assignees.fill');
    Route::post('/hod-assignees/{assignee}/fill', [HodAssignmentController::class, 'submitAssignee'])->name('hod-assignees.submit');

    Route::resource('appraisal-forms', AppraisalFormController::class)->except(['show']);
    Route::get('/appraisal-library', [AppraisalLibraryController::class, 'index'])->name('appraisal-library.index');
    Route::put('/appraisal-library', [AppraisalLibraryController::class, 'update'])->name('appraisal-library.update');
    Route::resource('key-behaviors', KeyBehaviorController::class)->except(['show', 'destroy']);
    Route::resource('staff', StaffMemberController::class)->only(['index', 'edit', 'update']);
    Route::get('/users/staff-link-search', [UserController::class, 'searchStaffForLink'])->name('users.staff-link-search');
    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::resource('roles', RoleController::class)->except(['show']);
});
