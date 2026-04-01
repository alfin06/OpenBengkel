<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderItem extends Model
{
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
