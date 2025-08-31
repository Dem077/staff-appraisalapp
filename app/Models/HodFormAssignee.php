<?php

namespace App\Models;

use App\Enum\HODFormassigneeType;
use Illuminate\Database\Eloquent\Model;

class HodFormassignee extends Model
{

    protected $table = 'hod_form_assignees';

    protected $fillable = [
        'assignee_type',
        'assignee_id',
        'status',
        'assignee_comment',
        'forms_assigned_to_hod_id',
    ];

    protected function casts(): array
    {
        return [
            'assignee_type' =>HODFormassigneeType::class,
        ];
    }

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
        return $this->hasMany(HodFormAssigneeEntry::class, 'hod_form_assignee_id');
    }
}
