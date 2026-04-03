<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UserTracking;

class WorkOrderItem extends Model
{
    use UserTracking;
    
    protected $fillable = [
        'work_order_id',
        'task_name',
        'mechanic_notes',
        'is_completed',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
