<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'role', 'password', 'parent_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasRoles, HasFactory, Notifiable {
        HasRoles::assignRole as traitAssignRole;
    }

    public function assignRole(...$roles)
    {
        // 1. Execute Spatie's default pivot table relationship save
        $result = $this->traitAssignRole(...$roles);

        // 2. Automatically sync the primary role name back to your user text column
        $this->forceFill([
            'role' => $this->getRoleNames()->first() ?? 'staff'
        ])->save();

        return $result;
    }

    // A manager can have many staff members
    public function staff()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    // A staff member belongs to a creator/manager
    public function creator()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
