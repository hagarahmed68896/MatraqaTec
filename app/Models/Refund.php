<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = [
        'refund_number',
        'order_id',
        'amount',
        'status',
        'reason',
        'admin_note',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
