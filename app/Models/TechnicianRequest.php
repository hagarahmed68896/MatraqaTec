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

    /**
     * Set the phone attribute.
     * Normalizes the phone number by removing non-digits and leading country code/zero.
     *
     * @param string $value
     * @return void
     */
    public function setPhoneAttribute($value)
    {
        if ($value) {
            $phone = preg_replace('/[^0-9]/', '', $value);
            if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
            if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
            $this->attributes['phone'] = $phone;
        }
    }

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
