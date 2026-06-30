<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'code',
        'price',
        'image',
        'icon',
        'description_ar',
        'description_en',
        'parent_id',
        'city_id',
        'is_featured',
        'districts',
    ];

    protected function casts(): array
    {
        return [
            'districts' => 'array',
        ];
    }

    protected $appends = ['image_url', 'icon_url', 'vat_rate', 'platform_commission', 'company_percentage', 'final_price'];

    public function getVatRateAttribute()
    {
        return (float) \App\Models\Setting::getByKey('vat_rate_services', 15);
    }

    public function getPlatformCommissionAttribute()
    {
        return (float) \App\Models\Setting::getByKey('platform_commission_services', 15);
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
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http')) return $this->image;
        if (str_starts_with($this->image, 'uploads/')) return asset($this->image);
        return asset($this->image); // Fallback to direct asset
    }

    public function getIconUrlAttribute()
    {
        if (!$this->icon) return null;
        if (str_starts_with($this->icon, 'http')) return $this->icon;
        if (str_starts_with($this->icon, 'uploads/')) return asset($this->icon);
        return asset($this->icon); // Fallback to direct asset
    }

    public function parent()
    {
        return $this->belongsTo(Service::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Service::class, 'parent_id');
    }

    public function favoritedBy()
    {
        return $this->hasMany(Favorite::class);
    }

    public function technicians()
    {
        return $this->hasMany(Technician::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class)->withDefault([
            'id' => 1,
            'name_ar' => 'الدمام',
            'name_en' => 'Dammam',
        ]);
    }

    public function maintenanceCompanies()
    {
        return $this->belongsToMany(MaintenanceCompany::class, 'maintenance_company_service');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
