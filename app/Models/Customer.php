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
        'phone',
        'phone_secondary',
        'address',
    ];

    /**
     * Get the vehicles for the customer.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
