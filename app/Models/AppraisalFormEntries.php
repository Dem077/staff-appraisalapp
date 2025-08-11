<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalFormEntries extends Model
{
    protected $fillable = [
        'appraisal_form_assigned_to_staff_id',
        'question_id',
        'answer',
        'staff_score',
        'supervisor_score',
        'hidden',
    ];

    public function appraisalFormAssignedToStaff()
    {
        return $this->belongsTo(AppraisalFormAssignedToStaff::class);
    }

    public function question()
    {
        return $this->belongsTo(AppraisalFormQuestions::class, 'question_id');
    }
}
