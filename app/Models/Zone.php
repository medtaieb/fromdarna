<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'ar_name',
        'description',
        'is_active',
        'delivery_fee_millimes',
        'estimated_delivery_minutes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'delivery_fee_millimes' => 'integer',
        'estimated_delivery_minutes' => 'integer',
    ];

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }
}

