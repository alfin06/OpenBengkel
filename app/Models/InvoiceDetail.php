<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UserTracking;

class InvoiceDetail extends Model
{
    use UserTracking;
    protected $fillable = ['invoice_id', 'item_name', 'type', 'part_id', 'qty', 'price', 'discount', 'subtotal'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

}
