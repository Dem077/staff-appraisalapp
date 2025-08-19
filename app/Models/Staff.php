<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Authenticatable implements FilamentUser, HasAvatar
{
    use SoftDeletes,HasRoles, Notifiable, HasFactory;

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
        'theme_color'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
        'joined_date' => 'date',
        'is_annual_applicable' => 'boolean',
    ];

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar_url) {
            return asset('storage/' . $this->avatar_url);
        } else {
            $hash = md5(strtolower(trim($this->email)));

            return 'https://www.gravatar.com/avatar/' . $hash . '?d=mp&r=g&s=250';
        }
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function appraisalFormAssigned()
    {
        return $this->hasMany(AppraisalFormAssignedToStaff::class , 'staff_id');
    }

    public function supervisor()
    {
        return $this->hasMany(AppraisalFormAssignedToStaff::class, 'supervisor_id');
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
