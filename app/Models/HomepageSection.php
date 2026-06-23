<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageSection extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'type', 'collection_id', 'product_limit', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'product_limit' => 'integer',
        'sort_order' => 'integer',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
