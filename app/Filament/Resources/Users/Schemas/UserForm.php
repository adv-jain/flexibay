<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Filament\Forms\Get;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('parent_id')
                    ->default(Auth::id()),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Select::make('role')
                    ->options([
                        'manager' => 'Manager/Owner',
                        'staff' => 'Staff',
                    ])
                    // FIX 1: If logged-in user is a manager, default to staff
                    ->default(fn() => Auth::user()?->hasRole('manager') ? 'staff' : null)

                    // FIX 2: Only disable the dropdown if the logged-in user is a manager. 
                    // This ensures Admins can still select 'manager' or 'admin'!
                    ->disabled(fn() => Auth::user()?->hasRole('manager'))
                    ->dehydrated()

                    // FIX 3: Make $record nullable (?) to support the "Create User" page
                    ->afterStateUpdated(function (string $state, ?Model $record) {
                        // If the record doesn't exist yet (Create page), do nothing.
                        // Spatie roles will be handled seamlessly on save using model observers/events.
                        if (! $record) {
                            return;
                        }

                        // If the record exists (Edit page), sync the roles normally
                        $record->syncRoles([$state]);
                    }),

            ]);
    }
}
