<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'maintenance_company_id',
        'category_id',
        'service_id',
        'years_experience',
        'availability_status',
        'order_count',
        'name_en',
        'name_ar',
        'bio_en',
        'bio_ar',
        'image',
        'national_id',
        'districts',
    ];

    protected $casts = [
        'districts' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function maintenanceCompany()
    {
        return $this->belongsTo(MaintenanceCompany::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
