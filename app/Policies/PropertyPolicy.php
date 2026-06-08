<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /**
     * Determine whether the user can view the navigation link and list page.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Determine whether the user can view a specific property detail view.
     */
    public function view(User $user, Property $property): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers and Staff can view if the property belongs to their team account group
        if ($user->hasRole('manager')) {
            $staffIds = User::where('parent_id', $user->id)->pluck('id')->toArray();
            return in_array($property->user_id, array_merge([$user->id], $staffIds));
        }

        if ($user->hasRole('staff')) {
            $managerId = $user->parent_id;
            $staffIds = User::where('parent_id', $managerId)->pluck('id')->toArray();
            return in_array($property->user_id, array_merge([$managerId], $staffIds));
        }

        return false;
    }

    /**
     * Determine whether the user can click create buttons or run creation cycles.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Determine whether the user can update a specific property entry record.
     */
    public function update(User $user, Property $property): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can edit their own properties or properties added by their staff
        if ($user->hasRole('manager')) {
            $staffIds = User::where('parent_id', $user->id)->pluck('id')->toArray();
            return in_array($property->user_id, array_merge([$user->id], $staffIds));
        }

        // Staff can edit properties belonging to their manager or team group
        if ($user->hasRole('staff')) {
            $managerId = $user->parent_id;
            $staffIds = User::where('parent_id', $managerId)->pluck('id')->toArray();
            return in_array($property->user_id, array_merge([$managerId], $staffIds));
        }

        return false;
    }

    /**
     * Determine whether the user can safely drop or delete a specific property.
     */
    public function delete(User $user, Property $property): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can delete team workspace properties
        if ($user->hasRole('manager')) {
            $staffIds = User::where('parent_id', $user->id)->pluck('id')->toArray();
            return in_array($property->user_id, array_merge([$user->id], $staffIds));
        }

        // OPTIONAL SECURITY CONSTRAINT: If you want staff to be able to delete team properties, 
        // keep this code. If staff should NOT delete properties, change this to: return false;
        if ($user->hasRole('staff')) {
            $managerId = $user->parent_id;
            $staffIds = User::where('parent_id', $managerId)->pluck('id')->toArray();
            return in_array($property->user_id, array_merge([$managerId], $staffIds));
        }

        return false;
    }

    /**
     * Determine whether the user can use bulk delete checkbox actions in Filament tables.
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Determine whether the user can run bulk records data state updates.
     */
    public function updateAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    public function restore(User $user, Property $property): bool
    {
        return false;
    }

    public function forceDelete(User $user, Property $property): bool
    {
        return false;
    }
}
