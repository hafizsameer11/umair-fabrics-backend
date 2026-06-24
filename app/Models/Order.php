<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_name', 'customer_phone', 'customer_phone_alt', 'customer_email',
        'shipping_address', 'city', 'billing_address', 'billing_city', 'notes', 'payment_method',
        'payment_status', 'fulfillment_status', 'subtotal', 'shipping_cost', 'total',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-'.strtoupper(substr(uniqid(), -8));
    }
}
