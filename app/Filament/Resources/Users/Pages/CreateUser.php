<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Runs automatically right after the User row is created in the database.
     */
    protected function afterCreate(): void
    {
        // Fetch the newly created User instance
        $user = $this->record;

        // Pull the text value saved in your custom 'role' table column
        $roleName = $user->role;

        if ($roleName) {
            // Assign the official Spatie role mapping connection seamlessly
            $user->assignRole($roleName);
        }
    }
}
