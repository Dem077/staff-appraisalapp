<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AppraisalFormAssignedToStaff;
use App\Models\Staff;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppraisalFormAssignedToStaffPolicy
{
    use HandlesAuthorization;

    protected function can($user, $ability)
    {
        if ($user instanceof User || $user instanceof Staff) {
            return $user->can($ability);
        }
        return false;
    }

    public function viewAny($user): bool
    {
        return $this->can($user, 'view_any_appraisal::form::assigned::to::staff');
    }

    public function view($user, AppraisalFormAssignedToStaff $appraisalFormAssignedToStaff): bool
    {
        return $this->can($user, 'view_appraisal::form::assigned::to::staff');
    }

    public function create($user): bool
    {
        return $this->can($user, 'create_appraisal::form::assigned::to::staff');
    }

    public function update($user, AppraisalFormAssignedToStaff $appraisalFormAssignedToStaff): bool
    {
        return $this->can($user, 'update_appraisal::form::assigned::to::staff');
    }

    public function delete($user, AppraisalFormAssignedToStaff $appraisalFormAssignedToStaff): bool
    {
        return $this->can($user, 'delete_appraisal::form::assigned::to::staff');
    }

    public function deleteAny($user): bool
    {
        return $this->can($user, 'delete_any_appraisal::form::assigned::to::staff');
    }

    public function forceDelete($user, AppraisalFormAssignedToStaff $appraisalFormAssignedToStaff): bool
    {
        return $this->can($user, '{{ ForceDelete }}');
    }

    public function forceDeleteAny($user): bool
    {
        return $this->can($user, '{{ ForceDeleteAny }}');
    }

    public function restore($user, AppraisalFormAssignedToStaff $appraisalFormAssignedToStaff): bool
    {
        return $this->can($user, '{{ Restore }}');
    }

    public function restoreAny($user): bool
    {
        return $this->can($user, '{{ RestoreAny }}');
    }

    public function replicate($user, AppraisalFormAssignedToStaff $appraisalFormAssignedToStaff): bool
    {
        return $this->can($user, '{{ Replicate }}');
    }

    public function reorder($user): bool
    {
        return $this->can($user, '{{ Reorder }}');
    }
}
