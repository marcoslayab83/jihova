<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreSale extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'branch_id',
        'user_id',
        'sale_number',
        'channel',
        'customer_name',
        'customer_phone',
        'payment_method',
        'payment_reference',
        'subtotal',
        'discount_amount',
        'total_amount',
        'notes',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'sold_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(StoreBranch::class, 'branch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StoreSaleItem::class, 'store_sale_id');
    }

    public function channelLabel(): string
    {
        return match ($this->channel) {
            'in_store' => 'Mostrador / Tienda Física',
            'whatsapp' => 'WhatsApp / Directo',
            'phone' => 'Llamada Telefónica',
            default => 'Venta Directa',
        };
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'cash_usd' => 'Efectivo Divisas ($)',
            'cash_ves' => 'Efectivo Bolívares (Bs)',
            'pago_movil' => 'Pago Móvil',
            'pos_card' => 'Punto de Venta / Tarjeta',
            'transfer' => 'Transferencia Bancaria',
            'binance_usdt' => 'Binance USDT / Cripto',
            default => 'Otro Método',
        };
    }
}
