<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormsAssignedToHod extends Model
{

    protected $fillable = [
        'assigned_date',
        'appraisal_form_id',
        'hod_id',
        'hod_comment',
    ];

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
