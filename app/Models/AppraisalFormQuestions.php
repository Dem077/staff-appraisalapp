<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalFormQuestions extends Model
{
    protected $fillable = [
        'appraisal_form_key_behavior_id',
        'behavioral_indicators',
    ];

    public function appraisalFormKeyBehavior()
    {
        return $this->belongsTo(AppraisalFormKeyBehavior::class, 'appraisal_form_key_behavior_id');
    }

    public function appraisalFormEntries()
    {
        return $this->hasMany(AppraisalFormEntries::class, 'question_id');
    }
}
