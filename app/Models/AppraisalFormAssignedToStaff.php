<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalFormAssignedToStaff extends Model
{
   protected $fillable = [
       'assigned_date',
       'appraisal_form_id',
       'staff_id',
       'supervisor_id',
       'supervisor_comment',
       'staff_comment',
       'appraisal_type',
       'status',
   ];

   public function staff()
   {
       return $this->belongsTo(Staff::class, 'staff_id');
   }

   public function supervisor()
   {
       return $this->belongsTo(Staff::class, 'supervisor_id');
   }

   public function appraisalForm()
   {
       return $this->belongsTo(AppraisalForm::class, 'appraisal_form_id');
   }

   public function appraisalFormEntries()
   {
       return $this->hasMany(AppraisalFormEntries::class, 'appraisal_assigned_to_staff_id');
   }
}
 