<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalFormEntries extends Model
{
    protected $fillable = [
        'appraisal_assigned_to_staff_id',
        'question_id',
        'staff_score',
        'supervisor_score',
        'hidden',
    ];

    public function appraisalFormAssignedToStaff()
    {
        return $this->belongsTo(AppraisalFormAssignedToStaff::class , 'appraisal_assigned_to_staff_id');
    }

    public function question()
    {
        return $this->belongsTo(AppraisalFormQuestions::class, 'question_id');
    }
}
