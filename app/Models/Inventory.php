<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'maintenance_company_id',
        'name_ar',
        'name_en',
        'image',
        'price',
        'quantity',
        'status',
    ];

    protected $appends = ['image_url', 'vat_rate', 'platform_commission', 'company_percentage', 'final_price'];

    public function getVatRateAttribute()
    {
        return (float) \App\Models\Setting::getByKey('vat_rate_parts', 15);
    }

    public function getPlatformCommissionAttribute()
    {
        return (float) \App\Models\Setting::getByKey('platform_commission_parts', 15);
    }

    public function getCompanyPercentageAttribute()
    {
        return 100 - $this->platform_commission;
    }

    public function getFinalPriceAttribute()
    {
        $price = (float) $this->price;
        $vat = $price * ($this->vat_rate / 100);
        $commission = $price * ($this->platform_commission / 100);
        return round($price + $vat + $commission, 2);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset($this->image) : null;
    }

    public function maintenanceCompany()
    {
        return $this->belongsTo(MaintenanceCompany::class);
    }
}
