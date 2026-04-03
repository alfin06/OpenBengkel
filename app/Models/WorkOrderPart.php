<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderPart extends Model
{
    protected $fillable = [
        'work_order_id', 
        'part_id', 
        'qty', 
        'price_sold', 
        'cost_at_sale'
    ];

    // Link back to the Inventory Item
    public function part(): BelongsTo 
    {
        // Explicitly define the foreign key (part_id)
        return $this->belongsTo(Part::class, 'part_id');
    }

    // Link back to the Work Order
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}