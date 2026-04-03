<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UserTracking;

class Mechanic extends Model
{
    use UserTracking;
    protected $fillable = ['name', 'phone', 'is_active'];

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
