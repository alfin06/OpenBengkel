<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'phone_office',
        'phone_whatsapp',
        'phone_sales',
        'sales_name',
        'email',
        'npwp',
        'notes'
    ];

    public function parts()
    {
        return $this->belongsToMany(Part::class)
                    ->withPivot('purchase_price', 'supplier_sku')
                    ->withTimestamps();
    }

    public function stockEntries(): HasMany
    {
        return $this->hasMany(StockEntry::class);
    }
}