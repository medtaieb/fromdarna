<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'language',
        'notify_orders',
        'notify_marketing',
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
        'notify_orders' => 'boolean',
        'notify_marketing' => 'boolean',
    ];

    /**
     * Adresses enregistrées du client
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Adresse de livraison par défaut
     */
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('is_default', true)->latestOfMany();
    }

    public function courierLocation(): HasOne
    {
        return $this->hasOne(CourierLocation::class);
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function assignedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'courier_id');
    }

    public function cashDrops(): HasMany
    {
        return $this->hasMany(CourierCashDrop::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(OrderIncident::class, 'courier_id');
    }

    /**
     * Montant total de cash détenu en main par le livreur (en millimes)
     */
    public function getHeldCashMillimes(): int
    {
        // Total cash encaissé sur les commandes livrées
        $collectedCash = $this->assignedOrders()
            ->where('status', 'DELIVERED')
            ->where('payment_method', 'cash')
            ->sum('total_millimes');

        // Total cash déjà remis au QG
        $droppedCash = $this->cashDrops()
            ->where('status', 'validated')
            ->sum('amount_millimes');

        $balance = $collectedCash - $droppedCash;
        return max(0, (int) $balance);
    }

    /**
     * Vérifie si le livreur a dépassé le plafond de cash de 150 TND (150 000 millimes)
     */
    public function isCashLimitExceeded(): bool
    {
        return $this->getHeldCashMillimes() >= 150000;
    }

    /**
     * Gains du livreur aujourd'hui (tarif fixe 2,500 TND par course livrée)
     */
    public function getTodayEarningsMillimes(): int
    {
        $count = $this->assignedOrders()
            ->where('status', 'DELIVERED')
            ->whereDate('delivered_at', today())
            ->count();

        return $count * 2500;
    }

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
