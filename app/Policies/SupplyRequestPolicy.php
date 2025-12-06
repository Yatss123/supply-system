<?php

namespace App\Policies;

use App\Models\SupplyRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SupplyRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SupplyRequest $supplyRequest): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SupplyRequest $supplyRequest): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SupplyRequest $supplyRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SupplyRequest $supplyRequest): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SupplyRequest $supplyRequest): bool
    {
        return true;
    }

    /**
     * Determine whether the user can approve the supply request.
     */
    public function approve(User $user, SupplyRequest $supplyRequest): bool
    {
        // Only administrators can approve pending requests
        return $supplyRequest->status === 'pending' && $user->hasAdminPrivileges();
    }

    /**
     * Determine whether the user can decline the supply request.
     */
    public function decline(User $user, SupplyRequest $supplyRequest): bool
    {
        // Only administrators can decline pending requests
        return $supplyRequest->status === 'pending' && $user->hasAdminPrivileges();
    }
}
