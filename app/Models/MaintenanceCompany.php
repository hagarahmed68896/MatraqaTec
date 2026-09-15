<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'city_id', // Defines Main City
        'name',
        'company_name_ar',
        'company_name_en',
        'commercial_record_number',
        'commercial_record_file',
        'tax_number',
        'address',
        'technician_count',
        'service_count',
        'order_count',
        'bank_name',
        'account_name',
        'account_number',
        'bank_address',
        'iban',
        'swift_code',
    ];

    protected $appends = ['commercial_record_url'];

    public function getCommercialRecordUrlAttribute()
    {
        if (!$this->commercial_record_file) return null;
        if (str_starts_with($this->commercial_record_file, 'http://') || str_starts_with($this->commercial_record_file, 'https://')) return $this->commercial_record_file;
        
        $cleanPath = ltrim($this->commercial_record_file, '/');
        if (str_starts_with($cleanPath, 'uploads/')) return asset($cleanPath);
        if (str_starts_with($cleanPath, 'storage/')) return asset($cleanPath);

        if (file_exists(public_path('uploads/' . $cleanPath))) return asset('uploads/' . $cleanPath);
        if (file_exists(public_path('storage/' . $cleanPath))) return asset('storage/' . $cleanPath);

        return asset('uploads/' . $cleanPath);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function technicians()
    {
        return $this->hasMany(Technician::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Technician::class);
    }

    public function financialSettlements()
    {
        return $this->hasMany(FinancialSettlement::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'maintenance_company_service')->withPivot('district_ids');
    }

    public function districts()
    {
        return $this->belongsToMany(District::class, 'maintenance_company_district');
    }

    public function schedules()
    {
        return $this->hasMany(CompanySchedule::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }
}
