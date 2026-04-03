<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UserTracking;

class Part extends Model
{
    use UserTracking;
    use HasFactory;

    // Add this block:
    protected $fillable = [
        'name',
        'sku',
        'buy_price',
        'sell_price',
        'stock',
        'type',
    ];
}