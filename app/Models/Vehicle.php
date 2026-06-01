<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Inertia\Inertia;
use Inertia\Response;
use App\Traits\UserTracking;

class Vehicle extends Model
{
    use SoftDeletes;
    use UserTracking;

    protected $fillable = [
        'customer_id', 
        'plate_number', 
        'brand', 
        'model', 
        'color', 
        'year',
        'chassis_number',
        'engine_number'
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

    public function scopeNeedsReminder($query)
    {
        // Fetch vehicles where the last service was 90 days ago or more
        // AND prevent spamming by making sure we don't remind them if they came recently
        return $query->where('last_serviced_at', '<=', now()->subMonths(3)->format('Y-m-d'))
                    ->with(['customer']);
    }
}