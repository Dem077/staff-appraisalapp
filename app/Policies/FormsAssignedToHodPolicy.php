<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;
use App\Models\FormsAssignedToHod;
use Illuminate\Auth\Access\HandlesAuthorization;

class FormsAssignedToHodPolicy
{
    use HandlesAuthorization;

    protected function can($user, $ability)
    {
        if ($user instanceof User || $user instanceof Staff) {
            return $user->can($ability);
        }
        return false;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAll( $user): bool
    {
        return $user->can('view_all_forms::assigned::to::hod');
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny( $user): bool
    {
        return $user->can('view_any_forms::assigned::to::hod');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view( $user, FormsAssignedToHod $formsAssignedToHod): bool
    {
        return $user->can('view_forms::assigned::to::hod');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create( $user): bool
    {
        return $user->can('create_forms::assigned::to::hod');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update( $user, FormsAssignedToHod $formsAssignedToHod): bool
    {
        return $user->can('update_forms::assigned::to::hod');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete( $user, FormsAssignedToHod $formsAssignedToHod): bool
    {
        return $user->can('delete_forms::assigned::to::hod');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny( $user): bool
    {
        return $user->can('delete_any_forms::assigned::to::hod');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete( $user, FormsAssignedToHod $formsAssignedToHod): bool
    {
        return $user->can('force_delete_forms::assigned::to::hod');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny( $user): bool
    {
        return $user->can('force_delete_any_forms::assigned::to::hod');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore( $user, FormsAssignedToHod $formsAssignedToHod): bool
    {
        return $user->can('restore_forms::assigned::to::hod');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny( $user): bool
    {
        return $user->can('{{ RestoreAny }}');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate( $user, FormsAssignedToHod $formsAssignedToHod): bool
    {
        return $user->can('{{ Replicate }}');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder( $user): bool
    {
        return $user->can('{{ Reorder }}');
    }
}
