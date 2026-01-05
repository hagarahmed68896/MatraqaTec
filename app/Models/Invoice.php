<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'invoice_number',
        'issue_date',
        'status',
        'amount',
        'tax',
        'total',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
