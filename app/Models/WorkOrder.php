<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Customer;
use App\Models\Vehicle;

class WorkOrder extends Model
{
    use UserTracking;
    use SoftDeletes;
    
    protected $fillable = [
        'vehicle_id',
        'driver_name',
        'odometer',
        'complaints',
        'status',
        'mechanic_id',
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
    public function parts()
    {
        return $this->belongsToMany(Part::class, 'work_order_parts')
                    ->withPivot('id', 'qty', 'price_sold')
                    ->withTimestamps();
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

    public function mechanic()
    {
        return $this->belongsTo(Mechanic::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
