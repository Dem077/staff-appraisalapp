<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HodFormassignee extends Model
{

    protected $fillable = [
        'assignee_type',
        'assignee_id',
        'assignee_comment',
        'forms_assigned_to_hod_id',
    ];

    public function formsAssignedToHod()
    {
        return $this->belongsTo(FormsAssignedToHod::class, 'forms_assigned_to_hod_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'assignee_id');
    }

    public function hodFormAssigneeEntries()
    {
        return $this->hasMany(HodFormAssigneeEntry::class, 'hod_assignee_id');
    }
}
