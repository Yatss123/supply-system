<?php

namespace App\Policies;

use App\Models\RestockRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RestockRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return true; // All authenticated users can view restock requests
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RestockRequest $restockRequest)
    {
        return true; // All authenticated users can view individual restock requests
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasAdminPrivileges(); // Only admin and super_admin can create
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RestockRequest $restockRequest)
    {
        return $user->hasAdminPrivileges(); // Only admin and super_admin can update
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RestockRequest $restockRequest)
    {
        return $user->hasAdminPrivileges();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RestockRequest $restockRequest)
    {
        return $user->hasAdminPrivileges();
    }
}