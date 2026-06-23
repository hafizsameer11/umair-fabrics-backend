<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingSetting extends Model
{
    protected $fillable = ['name', 'flat_rate', 'free_shipping_threshold', 'is_active'];

    protected $casts = [
        'flat_rate' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
