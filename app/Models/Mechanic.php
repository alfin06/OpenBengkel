<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UserTracking;

class Mechanic extends Model
{
    use UserTracking;
    use SoftDeletes;

    // Change status to is_active
    protected $fillable = ['name', 'phone', 'is_active', 'nik', 'ktp_path'];

    // Ensure Laravel treats it as a true/false boolean
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
