<?php

namespace App\Http\Controllers;

use App\Models\AppraisalFormAssignedToStaff;
use App\Models\FormsAssignedToHod;
use App\Support\AuthContext;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $staffId = AuthContext::staffId();

        $stats = [
            'pending_assignments' => 0,
            'pending_hod' => 0,
            'completed' => 0,
        ];

        if (AuthContext::canAny([
            'view_any_appraisal::form::assigned::to::staff',
            'view_appraisal::form::assigned::to::staff',
        ])) {
            $assignmentQuery = AppraisalFormAssignedToStaff::query();
            if (! AuthContext::can('view_all_appraisal::form::assigned::to::staff')) {
                $assignmentQuery->where(function ($q) use ($staffId) {
                    $q->where('supervisor_id', $staffId)
                        ->orWhere('staff_id', $staffId);
                });
            }

            $stats['pending_assignments'] = (clone $assignmentQuery)
                ->whereNotIn('status', ['complete', 'hr_comment'])
                ->count();
            $stats['completed'] = (clone $assignmentQuery)->where('status', 'complete')->count();

            $hodQuery = FormsAssignedToHod::query();
            if (! AuthContext::can('view_all_forms::assigned::to::hod')) {
                $hodQuery->where(function ($q) use ($staffId) {
                    $q->where('hod_id', $staffId)
                        ->orWhere('supervisor_id', $staffId);
                });
            }
            $stats['pending_hod'] = (clone $hodQuery)->where('status', '!=', 'complete')->count();
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }
}
