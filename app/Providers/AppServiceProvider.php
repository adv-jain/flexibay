<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

use App\Models\User;
use App\Policies\UserPolicy;

use App\Models\Property;
use App\Models\Room;
use App\Policies\PropertyPolicy;
use App\Policies\RoomPolicy;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Explicitly register the policies
        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        // Automatically grants the "admin" role full access bypass across all policies
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        TextInput::macro('rupees', function () {
            return $this
                ->numeric()
                ->prefix('₹');
        });

        TextColumn::macro('rupees', function () {
            return $this
                ->formatStateUsing(
                    fn($state) =>
                    $state ? '₹ ' . number_format($state, 2) : '₹ 0.00'
                )
                ->suffix('');
        });
    }
}
