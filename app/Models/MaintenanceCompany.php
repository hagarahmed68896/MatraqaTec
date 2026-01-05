<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'city_id',
        'company_name_ar',
        'company_name_en',
        'commercial_record_number',
        'commercial_record_file',
        'tax_number',
        'address',
        'technician_count',
        'service_count',
        'order_count',
    ];

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
}
