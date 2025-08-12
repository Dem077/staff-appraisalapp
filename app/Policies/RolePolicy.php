<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
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
        return $this->can($user, 'view_any_role');
    }

    public function view($user, Role $role): bool
    {
        return $this->can($user, 'view_role');
    }

    public function create($user): bool
    {
        return $this->can($user, 'create_role');
    }

    public function update($user, Role $role): bool
    {
        return $this->can($user, 'update_role');
    }

    public function delete($user, Role $role): bool
    {
        return $this->can($user, 'delete_role');
    }

    public function deleteAny($user): bool
    {
        return $this->can($user, 'delete_any_role');
    }

    public function forceDelete($user, Role $role): bool
    {
        return $this->can($user, '{{ ForceDelete }}');
    }

    public function forceDeleteAny($user): bool
    {
        return $this->can($user, '{{ ForceDeleteAny }}');
    }

    public function restore($user, Role $role): bool
    {
        return $this->can($user, '{{ Restore }}');
    }

    public function restoreAny($user): bool
    {
        return $this->can($user, '{{ RestoreAny }}');
    }

    public function replicate($user, Role $role): bool
    {
        return $this->can($user, '{{ Replicate }}');
    }

    public function reorder($user): bool
    {
        return $this->can($user, '{{ Reorder }}');
    }
}
