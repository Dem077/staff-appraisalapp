<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalFormKeyBehavior extends Model
{
    protected $fillable = [
        'name',
        'appraisal_form_category_id',
    ];

    public function appraisalFormCategory()
    {
        return $this->belongsTo(AppraisalFormCategory::class, 'appraisal_form_category_id');
    }

    public function appraisalFormQuestions()
    {
        return $this->hasMany(AppraisalFormQuestions::class, 'appraisal_form_key_behavior_id');
    }
}
 