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
        'service_ids' => 'array',
    ];

    protected static function booted()
    {
        static::created(function ($request) {
            $name = $request->name ?? $request->name_ar ?? $request->name_en ?? 'فني جديد';
            Notification::notifyAdmins([
                'type' => 'technician_request',
                'title_ar' => 'طلب انضمام فني جديد',
                'title_en' => 'New Technician Join Request',
                'body_ar' => "قدم الفني {$name} طلب انضمام جديد للمنصة.",
                'body_en' => "Technician {$name} submitted a new join request.",
                'data' => [
                    'type' => 'technician_request',
                    'request_id' => $request->id,
                    'name' => $name,
                    'phone' => $request->phone,
                ]
            ]);
        });
    }

    protected $appends = ['photo_url', 'iqama_photo_url', 'services'];

    public function getServicesAttribute()
    {
        if (!$this->service_ids || !is_array($this->service_ids)) {
            if ($this->service_id) {
                return Service::where('id', $this->service_id)->get();
            }
            return collect();
        }
        return Service::whereIn('id', $this->service_ids)->get();
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset($this->photo) : null;
    }

    public function getIqamaPhotoUrlAttribute()
    {
        return $this->iqama_photo ? asset($this->iqama_photo) : null;
    }


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
