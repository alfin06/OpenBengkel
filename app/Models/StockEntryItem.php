<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockEntryItem extends Model
{
    protected $fillable = [
        'stock_entry_id',
        'part_id',
        'qty',
        'purchase_price'
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function stockEntry(): BelongsTo
    {
        return $this->belongsTo(StockEntry::class);
    }
}
