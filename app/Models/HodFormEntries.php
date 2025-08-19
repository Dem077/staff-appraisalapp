<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HodFormEntries extends Model
{
    protected $fillable = [
        'question_id',
        'hod_assignee_id',
        'score',
        'comment',
        'hidden',
    ];

    public function formsAssignedToHod()
    {
        return $this->belongsTo(FormsAssignedToHod::class, 'forms_assigned_to_hod_id');
    }

    public function question()
    {
        return $this->belongsTo(AppraisalFormQuestions::class, 'question_id');
    }
}