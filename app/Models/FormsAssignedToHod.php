<?php

namespace App\Models;

use App\Enum\HODFormassigneeStatus;
use Illuminate\Database\Eloquent\Model;

class FormsAssignedToHod extends Model
{

    protected $fillable = [
        'assigned_date',
        'appraisal_form_id',
        'hod_id',
        'status',
        'hod_comment',
    ];

    protected function casts(): array
    {
        return [
            'status' =>HODFormassigneeStatus::class,
        ];
    }
    public function appraisalForm()
    {
        return $this->belongsTo(AppraisalForm::class, 'appraisal_form_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'hod_id');
    }

    public function hodFormAssignees()
    {
        return $this->hasMany(HodFormAssignee::class, 'forms_assigned_to_hod_id');
    }

    public function hodFormEntries()
    {
        return $this->hasMany(HodFormEntries::class, 'forms_assigned_to_hod_id');
    }

}
