<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HodFormAssigneeEntry extends Model
{
    protected $fillable = [
        'question_id',
        'hod_form_assignee_id',
        'score',
        'comment',
        'hidden',
    ];

    public function hodFormAssignee()
    {
        return $this->belongsTo(HodFormAssignee::class, 'hod_form_assignee_id');
    }

    public function question()
    {
        return $this->belongsTo(AppraisalFormQuestions::class, 'question_id');
    }

}
