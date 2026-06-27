<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingSetting extends Model
{
    protected $fillable = [
        'name', 'flat_rate', 'weight_min_grams', 'weight_max_grams', 'base_fee',
        'extra_step_grams', 'extra_step_fee', 'free_shipping_threshold', 'is_active',
    ];

    protected $casts = [
        'flat_rate' => 'decimal:2',
        'base_fee' => 'decimal:2',
        'extra_step_fee' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'weight_min_grams' => 'integer',
        'weight_max_grams' => 'integer',
        'extra_step_grams' => 'integer',
        'is_active' => 'boolean',
    ];
}
