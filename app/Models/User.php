<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'status',
        'password',
        'otp_code',
        'otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function hasRole(...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isPartner(): bool
    {
        return in_array($this->role, ['partner_owner', 'partner_staff'], true);
    }

    public function isCourier(): bool
    {
        return $this->role === 'courier';
    }

    public function isOps(): bool
    {
        return in_array($this->role, ['ops', 'admin', 'finance'], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
