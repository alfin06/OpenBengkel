<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\SoftDeletes;

class Part extends Model
{
    use UserTracking;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'sku', 'buy_price', 'sell_price', 'discount_percent', 'stock', 'type', 'min_stock'];

    public function getFinalPriceAttribute()
    {
        if ($this->discount_percent > 0) {
            return $this->sell_price * (1 - ($this->discount_percent / 100));
        }
        return $this->sell_price;
    }

    protected $appends = ['final_price'];
}