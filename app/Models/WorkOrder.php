<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    protected $fillable = [
        'vehicle_id',
        'driver_name',
        'odometer',
        'complaints',
        'status',
    ];

    public function invoice() {
        return $this->hasOne(Invoice::class);
    }

    public function vehicle() {
        return $this->belongsTo(Vehicle::class);
    }

    public function items(): HasMany{
        return $this->hasMany(WorkOrderItem::class);
    }

    public function tasks() {
        return $this->hasMany(WorkOrderItem::class);
    }

    // These are the Parts (Oli, Filter, etc.)
    public function parts(): HasMany 
    {
        return $this->hasMany(WorkOrderPart::class, 'work_order_id');
    }

    protected $appends = ['parts_total'];

    public function getPartsTotalAttribute()
    {
        // 1. Get the parts as a Collection (not a Query)
        // 2. Use collect() to ensure the IDE knows we are in "Collection Mode"
        return collect($this->parts)->sum(function($item) {
            return (float) ($item->price_sold * $item->qty);
        });
    }
}
