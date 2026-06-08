<?php

namespace App\Policies;

use App\Models\user;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, user $model): bool
    {
        return $user->hasRole('admin', 'manager');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function updateAny(User $user, user $model): bool
    {
        if ($user->hasAnyRole(['admin', 'manager'])) return true;

        // Managers can only edit if they created the record and the record is a staff member
        return $user->hasRole('manager') && $model->parent_id === $user->id && $model->role === 'staff';
    }

    // Controls who can delete a specific user profile row
    public function delete(User $user, User $model): bool
    {
        if ($user->hasAnyRole(['admin', 'manager'])) return true;

        return $user->hasRole('manager') && $model->parent_id === $user->id && $model->role === 'staff';
    }

    /**
     * Determine whether the user can delete the model.
     */


    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, user $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, user $model): bool
    {
        return false;
    }
}
