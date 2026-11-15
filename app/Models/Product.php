<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price_millimes',
        'is_available',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'price_millimes' => 'integer',
        'is_available' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class)->orderBy('sort_order');
    }

    public function store(): BelongsTo
    {
        return $this->category->store();
    }

    // Accessors de compatibilité
    public function getPriceAttribute(): int
    {
        return $this->price_millimes;
    }

    public function getDescAttribute(): ?string
    {
        return $this->description;
    }

    public function getOkAttribute(): bool
    {
        return $this->is_available;
    }
}
