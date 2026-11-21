<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierCashDrop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount_millimes',
        'status',
        'reference',
        'notes',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'amount_millimes' => 'integer',
        'validated_at'    => 'datetime',
    ];

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public static function generateRef(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        return 'DROP-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
