<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UserTracking;

class InvoiceDetail extends Model
{
    use UserTracking;
    protected $fillable = ['invoice_id', 'item_name', 'qty', 'price', 'subtotal'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
