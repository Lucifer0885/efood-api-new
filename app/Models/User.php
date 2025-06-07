<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Address;
use App\Models\Role;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Enums\RoleCode;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        public function canAccessPanel(Panel $panel): bool
    {
        $panel_id = $panel->getId();
        if ($panel_id === "admin") {
            $role = $this->roles()->where('role_id', RoleCode::{$panel_id})->first();
            return !is_null($role);
        }elseif ($panel_id === "merchant") {
            // $role = $this->roles()->where('role_id', RoleCode::{$panel_id})->first();
            // return !is_null($role);
            return true;
        }
        return false;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class,);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function sockets(): HasMany
    {
        return $this->hasMany(UserSocket::class);
    }
}
