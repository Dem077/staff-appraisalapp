<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalFormCategory extends Model
{
    protected $fillable = [
        'name',
    ];

    public function appraisalFormKeyBehaviors()
    {
        return $this->hasMany(AppraisalFormKeyBehavior::class, 'appraisal_form_category_id');
    }

    public function appraisalForms()
    {
        return $this->hasMany(AppraisalForm::class, 'appraisal_form_category_id');
    }

}
