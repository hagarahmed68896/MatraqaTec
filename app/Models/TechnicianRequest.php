<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'name_ar',
        'email',
        'phone',
        'photo',
        'iqama_photo',
        'company_name',
        'maintenance_company_id',
        'service_id',
        'category_id',
        'years_experience',
        'bio_en',
        'bio_ar',
        'districts',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'districts' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function category()
    {
        return $this->belongsTo(Service::class, 'category_id');
    }

    public function maintenanceCompany()
    {
        return $this->belongsTo(MaintenanceCompany::class);
    }
}
