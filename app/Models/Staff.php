<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Staff extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $guard_name = 'staff';

    protected $fillable = [
        'name',
        'api_id',
        'email',
        'email_verified_at',
        'emp_no',
        'gender',
        'designation',
        'mobile',
        'phone',
        'department_id',
        'active',
        'location_id',
        'nid',
        'supervisor_id',
        'joined_date',
        'is_annual_applicable',
        'profile_photo_path',
        'profile_photo_url',
        'external_id',
        'theme',
        'theme_color',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
        'joined_date' => 'date',
        'is_annual_applicable' => 'boolean',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function avatarUrl(): string
    {
        if ($this->profile_photo_url) {
            return $this->profile_photo_url;
        }

        return 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($this->email ?? ''))).'?d=mp&r=g&s=250';
    }

    public function appraisalFormAssigned()
    {
        return $this->hasMany(AppraisalFormAssignedToStaff::class, 'staff_id');
    }

    public function supervisor()
    {
        return $this->hasMany(AppraisalFormAssignedToStaff::class, 'supervisor_id');
    }

    public function supervisorhod()
    {
        return $this->hasMany(FormsAssignedToHod::class, 'supervisor_id');
    }

    public function formsAssignedToHod()
    {
        return $this->hasMany(FormsAssignedToHod::class, 'hod_id');
    }

    public function hodAssignees()
    {
        return $this->hasMany(HodFormAssignee::class, 'assignee_id');
    }
}
