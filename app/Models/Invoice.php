<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    // These are the only columns allowed to be saved via Invoice::create()
    protected $fillable = [
        'work_order_id',
        'parts_total',
        'grand_total',
        'discount',
        'payment_method',
        'notes',
        'status',
        'due_date',
        'amount_paid',
        'customer_reference'
    ];

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}