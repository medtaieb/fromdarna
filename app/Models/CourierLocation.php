<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'heading',
        'speed',
        'battery',
        'is_online',
        'last_ping_at',
    ];

    protected $casts = [
        'latitude'     => 'float',
        'longitude'    => 'float',
        'heading'      => 'float',
        'speed'        => 'float',
        'battery'      => 'integer',
        'is_online'    => 'boolean',
        'last_ping_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
