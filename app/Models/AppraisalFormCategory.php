<?php

namespace App\Models;

use App\Enum\AppraisalFormCategoryType;
use App\Enum\AppraisalFormLevel;
use App\Enum\AppraisalFormType;
use Illuminate\Database\Eloquent\Model;

class AppraisalFormCategory extends Model
{
    protected $fillable = [
        'name',
        'type',
        'form_type',
        'appraisal_form_id',
        'sort_order',
    ];
    protected function casts(): array
    {
        return [
            'type' => AppraisalFormCategoryType::class,
            'form_type' => AppraisalFormType::class,
        ];
    }

    public function appraisalFormKeyBehaviors()
    {
        return $this->hasMany(AppraisalFormKeyBehavior::class, 'appraisal_form_category_id')
            ->orderBy('sort_order')
            ->orderBy('id');
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
