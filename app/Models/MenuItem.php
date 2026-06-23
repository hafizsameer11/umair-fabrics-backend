<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    protected $fillable = ['menu_id', 'label', 'url', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
