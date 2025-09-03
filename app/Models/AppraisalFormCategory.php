<?php

namespace App\Models;

use App\Enum\AppraisalFormCategoryType;
use App\Enum\HODFormassigneeType;
use Illuminate\Database\Eloquent\Model;

class AppraisalFormCategory extends Model
{
    protected $fillable = [
        'name',
        'type',
    ];
    protected function casts(): array
    {
        return [
            'type' => AppraisalFormCategoryType::class,
        ];
    }

    public function appraisalFormKeyBehaviors()
    {
        return $this->hasMany(AppraisalFormKeyBehavior::class, 'appraisal_form_category_id');
    }

    public function appraisalForms()
    {
        return $this->belongsToMany(
            AppraisalForm::class,
            'appraisal_form_category_form',
            'appraisal_form_category_id',
            'appraisal_form_id'
        );
    }

}
