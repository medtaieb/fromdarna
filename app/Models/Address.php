<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'city',
        'street_address',
        'landmark',
        'building',
        'floor',
        'door',
        'latitude',
        'longitude',
        'contact_phone',
        'is_default',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Texte complet formaté pour le livreur
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = [];

        if ($this->city) {
            $parts[] = $this->city;
        }

        if ($this->street_address) {
            $parts[] = $this->street_address;
        }

        if ($this->building) {
            $parts[] = 'Bâtiment ' . $this->building;
        }

        if ($this->floor) {
            $parts[] = 'Étage ' . $this->floor;
        }

        if ($this->door) {
            $parts[] = 'Porte ' . $this->door;
        }

        if ($this->landmark) {
            $parts[] = 'Repère : ' . $this->landmark;
        }

        return implode(' · ', $parts);
    }
}
