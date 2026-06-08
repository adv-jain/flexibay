<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    /**
     * Determine whether the user can view the navigation link and list page.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Determine whether the user can view a specific room record.
     */
    public function view(User $user, Room $room): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can view if the room belongs to their team account group
        if ($user->hasRole('manager')) {
            $staffIds = User::where('parent_id', $user->id)->pluck('id')->toArray();
            return in_array($room->user_id, array_merge([$user->id], $staffIds));
        }

        // Staff can view if the room belongs to their manager or team peers
        if ($user->hasRole('staff')) {
            $managerId = $user->parent_id;
            $staffIds = User::where('parent_id', $managerId)->pluck('id')->toArray();
            return in_array($room->user_id, array_merge([$managerId], $staffIds));
        }

        return false;
    }

    /**
     * Determine whether the user can create rooms.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Determine whether the user can update a specific room record.
     */
    public function update(User $user, Room $room): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can edit their own rooms or rooms added by their staff
        if ($user->hasRole('manager')) {
            $staffIds = User::where('parent_id', $user->id)->pluck('id')->toArray();
            return in_array($room->user_id, array_merge([$user->id], $staffIds));
        }

        // Staff can edit rooms belonging to their manager's team group
        if ($user->hasRole('staff')) {
            $managerId = $user->parent_id;
            $staffIds = User::where('parent_id', $managerId)->pluck('id')->toArray();
            return in_array($room->user_id, array_merge([$managerId], $staffIds));
        }

        return false;
    }

    /**
     * Determine whether the user can delete a specific room record.
     */
    public function delete(User $user, Room $room): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can delete team workspace rooms
        if ($user->hasRole('manager')) {
            $staffIds = User::where('parent_id', $user->id)->pluck('id')->toArray();
            return in_array($room->user_id, array_merge([$user->id], $staffIds));
        }

        // Staff can delete rooms belonging to their team workspace group
        if ($user->hasRole('staff')) {
            $managerId = $user->parent_id;
            $staffIds = User::where('parent_id', $managerId)->pluck('id')->toArray();
            return in_array($room->user_id, array_merge([$managerId], $staffIds));
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

    public function restore(User $user, Room $room): bool
    {
        return false;
    }

    public function forceDelete(User $user, Room $room): bool
    {
        return false;
    }
}
