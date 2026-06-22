<?php

namespace App\Models;

use App\Enum\AppraisalFormLevel;
use App\Enum\AppraisalFormType;
use Illuminate\Database\Eloquent\Model;

class AppraisalForm extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'level',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'type' => AppraisalFormType::class,
            'level' => AppraisalFormLevel::class,
        ];
    }

    public function appraisalFormCategories()
    {
        return $this->belongsToMany(
            AppraisalFormCategory::class,
            'appraisal_form_category_form',
            'appraisal_form_id',
            'appraisal_form_category_id'
        )->orderBy('appraisal_form_categories.sort_order')
            ->orderBy('appraisal_form_categories.id');
    }

    public function appraisalFormAssigned()
    {
        return $this->hasMany(AppraisalFormAssignedToStaff::class, 'appraisal_form_id');
    }

    
}
 