<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\UserTracking;

class Customer extends Model
{
    use HasFactory;

    use UserTracking;

    protected $fillable = [
        'name', 
        'phone_1', 
        'phone_2', 
        'phone_3', 
        'address', 
        'notes'
    ];

    /**
     * Get the vehicles for the customer.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function getServiceCountAttribute()
    {
        // Sum the number of work orders across all vehicles owned by this customer
        return $this->vehicles()->withCount('workOrders')->get()->sum('work_orders_count');
    }

    // Make sure to append it so it's included in the JSON sent to Vue
    protected $appends = ['service_count'];
}
