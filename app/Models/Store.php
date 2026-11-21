<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'user_id',
        'name',
        'slug',
        'kind',
        'emoji',
        'c1',
        'c2',
        'address',
        'latitude',
        'longitude',
        'avg_prep_minutes',
        'min_order_millimes',
        'commission_bps',
        'is_open',
        'phone',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'avg_prep_minutes' => 'integer',
        'min_order_millimes' => 'integer',
        'commission_bps' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('sort_order');
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(Product::class, Category::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Accessors de compatibilité avec les composants existants (store-card, etc.)
    public function getOpenAttribute(): bool
    {
        return $this->is_open;
    }

    public function getEtaAttribute(): int
    {
        return $this->avg_prep_minutes + 15;
    }

    public function getMinAttribute(): int
    {
        return $this->min_order_millimes;
    }
}
