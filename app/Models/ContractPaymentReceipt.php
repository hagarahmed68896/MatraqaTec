<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractPaymentReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'receipt_file',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
