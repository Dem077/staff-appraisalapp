<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalForm extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
    ];

    public function appraisalFormCategories()
    {
        return $this->belongsToMany(
            AppraisalFormCategory::class,
            'appraisal_form_category_form',
            'appraisal_form_id',
            'appraisal_form_category_id'
        );
    }

    public function appraisalFormAssigned()
    {
        return $this->hasMany(AppraisalFormAssignedToStaff::class, 'appraisal_form_id');
    }

    
}
 