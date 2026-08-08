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

    protected $appends = ['zatca_qr_code'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getZatcaQrCodeAttribute()
    {
        $sellerName = \App\Models\Setting::getByKey('company_name_ar', 'شركة مطرقة تك للخدمات');
        $vatNumber = \App\Models\Setting::getByKey('company_vat_number', '300012345600003');
        
        // Use invoice created_at timestamp
        $timestamp = $this->created_at ? $this->created_at->toIso8601String() : now()->toIso8601String();
        
        $totalWithVat = number_format((float)$this->total, 2, '.', '');
        $vatTotal = number_format((float)$this->tax, 2, '.', '');

        $tlv = $this->buildTlvString(1, $sellerName);
        $tlv .= $this->buildTlvString(2, $vatNumber);
        $tlv .= $this->buildTlvString(3, $timestamp);
        $tlv .= $this->buildTlvString(4, $totalWithVat);
        $tlv .= $this->buildTlvString(5, $vatTotal);

        return base64_encode($tlv);
    }

    private function buildTlvString($tag, $value)
    {
        $valueStr = (string)$value;
        $length = strlen($valueStr);
        return chr($tag) . chr($length) . $valueStr;
    }
}
