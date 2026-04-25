<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    public const PAYMENT_STATUSES = ['unpaid', 'paid', 'refunded'];

    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'customer_id',
        'delivery_person_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'customer_latitude',
        'customer_longitude',
        'subtotal',
        'total',
        'status',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'customer_id' => 'integer',
            'delivery_person_id' => 'integer',
            'customer_latitude' => 'float',
            'customer_longitude' => 'float',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryPerson(): BelongsTo
    {
        return $this->belongsTo(DeliveryPerson::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('id');
    }

    /**
     * Build a Google Maps URL the courier can tap to open native Maps.
     * Prefers the GPS coords; falls back to the text shipping address.
     * The link works on iOS (universal Google Maps URL) and Android.
     */
    public function mapsUrl(): ?string
    {
        if ($this->customer_latitude !== null && $this->customer_longitude !== null) {
            return 'https://www.google.com/maps?q='
                . rawurlencode($this->customer_latitude . ',' . $this->customer_longitude);
        }

        if (! empty($this->shipping_address)) {
            return 'https://www.google.com/maps?q=' . rawurlencode((string) $this->shipping_address);
        }

        return null;
    }
}
