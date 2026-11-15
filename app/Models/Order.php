<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref', 'user_id', 'store_id', 'courier_id', 'zone_id',
        'status', 'accepted_at', 'ready_at', 'picked_up_at', 'delivered_at',
        'payment_method', 'payment_status',
        'subtotal_millimes', 'delivery_fee_millimes', 'total_millimes',
        'delivery_landmark', 'delivery_city', 'delivery_building',
        'delivery_floor', 'delivery_door', 'delivery_phone',
        'delivery_lat', 'delivery_lng', 'delivery_code',
        'customer_note', 'idempotency_key',
    ];

    protected $casts = [
        'subtotal_millimes'      => 'integer',
        'delivery_fee_millimes'  => 'integer',
        'total_millimes'         => 'integer',
        'delivery_lat'           => 'float',
        'delivery_lng'           => 'float',
        'accepted_at'            => 'datetime',
        'ready_at'               => 'datetime',
        'picked_up_at'           => 'datetime',
        'delivered_at'           => 'datetime',
    ];

    // ── Labels statuts ────────────────────────────────────────────────────
    public const STATUS_LABELS = [
        'PLACED'    => ['Reçue',               'o'],
        'ACCEPTED'  => ['En préparation',       'x'],
        'READY'     => ['Prête',                'g'],
        'PICKED_UP' => ['En route',             'g'],
        'DELIVERED' => ['Livrée',               'g'],
        'CANCELLED' => ['Annulée',              'r'],
        'REJECTED'  => ['Refusée',              'r'],
        'FAILED'    => ['Échec',                'r'],
    ];

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status][0] ?? $this->status;
    }

    public function statusBadgeType(): string
    {
        return self::STATUS_LABELS[$this->status][1] ?? 'o';
    }

    // ── Relations ─────────────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ── Générateur de référence unique ────────────────────────────────────
    public static function generateRef(): string
    {
        $date = now()->format('Ymd');
        $last = static::whereDate('created_at', today())->count() + 1;
        return 'FD-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}
