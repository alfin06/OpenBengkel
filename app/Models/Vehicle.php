<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Inertia\Inertia;
use Inertia\Response;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id', 
        'plate_number', 
        'brand', 
        'model', 
        'color', 
        'year'
    ];

    /**
     * Get the customer that owns the vehicle.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    // If you have Work Orders linked to this vehicle:
    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}