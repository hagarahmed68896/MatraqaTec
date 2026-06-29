<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'amount',
        'payment_method',
        'status',
        'transaction_id',
    ];

    protected static function booted()
    {
        static::saved(function ($payment) {
            if ($payment->status === 'completed' && $payment->order_id) {
                $order = $payment->order;
                if ($order) {
                    $invoiceNumber = 'INV-' . $order->order_number;
                    $total = $order->total_price;
                    $amount = round($total / 1.15, 2);
                    $tax = round($total - $amount, 2);

                    \App\Models\Invoice::updateOrCreate(
                        ['order_id' => $order->id],
                        [
                            'invoice_number' => $invoiceNumber,
                            'issue_date' => now(),
                            'status' => 'not_sent',
                            'amount' => $amount,
                            'tax' => $tax,
                            'total' => $total,
                        ]
                    );
                }
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
