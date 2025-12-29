<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformProfit extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'fees',
        'percentage',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
