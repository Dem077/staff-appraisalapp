<?php

namespace App\Http\Controllers;

use App\Models\AppraisalFormAssignedToStaff;
use App\Models\AppraisalFormEntries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facades\Pdf;

class AppraisalPdfController extends Controller
{
    public function generate($id)
    {
        $assignment = AppraisalFormAssignedToStaff::with(['appraisalForm', 'staff', 'supervisor'])->findOrFail($id);

        $entries = AppraisalFormEntries::where('appraisal_assigned_to_staff_id', $assignment->id)
            ->where('hidden', false)
            ->with(['question.appraisalFormKeyBehavior.appraisalFormCategory'])
            ->get();

        $data = [
            'assignment' => $assignment,
            'entries' => $entries,
        ];

        // Use the dompdf facade to generate PDF. Ensure barryvdh/laravel-dompdf is installed.
        $pdf = Pdf::loadView('pdfs.appraisal_result', $data)->setPaper('a4', 'portrait');

        $filename = 'appraisal_result_' . $assignment->id . '.pdf';

        return $pdf->stream($filename);
    }
}
