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

    protected $appends = ['receipt_url'];

    public function getReceiptUrlAttribute()
    {
        if (!$this->receipt_file) return null;
        if (filter_var($this->receipt_file, FILTER_VALIDATE_URL)) return $this->receipt_file;
        return asset($this->receipt_file);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
