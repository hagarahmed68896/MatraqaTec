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
        'name',
        'years_experience',
        'availability_status',
        'order_count',
        'name_en',
        'name_ar',
        'bio_en',
        'bio_ar',
        'image',
        'national_id',
        'national_id_image',
        'districts',
        'bank_name',
        'account_name',
        'account_number',
        'bank_address',
        'iban',
        'swift_code',
    ];

    protected $casts = [
        'districts' => 'array',
        'service_ids' => 'array',
    ];

    protected $appends = ['image_url', 'national_id_image_url', 'services'];

    public function getServicesAttribute()
    {
        if (!$this->service_ids || !is_array($this->service_ids)) {
            // Return existing single service for backward compatibility if multi-services is empty
            if ($this->service_id) {
                return Service::where('id', $this->service_id)->get();
            }
            return collect();
        }
        return Service::whereIn('id', $this->service_ids)->get();
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) return $this->image;
        
        $cleanPath = ltrim($this->image, '/');
        if (str_starts_with($cleanPath, 'uploads/')) return asset($cleanPath);
        if (str_starts_with($cleanPath, 'storage/')) return asset($cleanPath);

        if (file_exists(public_path('uploads/' . $cleanPath))) return asset('uploads/' . $cleanPath);
        if (file_exists(public_path('uploads/technicians/' . basename($cleanPath)))) return asset('uploads/technicians/' . basename($cleanPath));
        if (file_exists(public_path('uploads/technician_requests/photos/' . basename($cleanPath)))) return asset('uploads/technician_requests/photos/' . basename($cleanPath));
        if (file_exists(public_path('storage/' . $cleanPath))) return asset('storage/' . $cleanPath);

        return asset('uploads/' . $cleanPath);
    }

    public function getNationalIdImageUrlAttribute()
    {
        if (!$this->national_id_image) return null;
        if (str_starts_with($this->national_id_image, 'http://') || str_starts_with($this->national_id_image, 'https://')) return $this->national_id_image;

        $cleanPath = ltrim($this->national_id_image, '/');
        if (str_starts_with($cleanPath, 'uploads/')) return asset($cleanPath);
        if (str_starts_with($cleanPath, 'storage/')) return asset($cleanPath);

        if (file_exists(public_path('uploads/' . $cleanPath))) return asset('uploads/' . $cleanPath);
        if (file_exists(public_path('uploads/technicians/ids/' . basename($cleanPath)))) return asset('uploads/technicians/ids/' . basename($cleanPath));
        if (file_exists(public_path('uploads/technician_requests/iqamas/' . basename($cleanPath)))) return asset('uploads/technician_requests/iqamas/' . basename($cleanPath));
        if (file_exists(public_path('storage/' . $cleanPath))) return asset('storage/' . $cleanPath);

        return asset('uploads/' . $cleanPath);
    }

    public function getDistrictsAttribute($value)
    {
        $ids = is_array($value) ? $value : json_decode($value, true);
        
        if (!$ids || !is_array($ids)) {
            return [];
        }

        $locale = app()->getLocale();
        return \App\Models\District::whereIn('id', $ids)
            ->get(['id', 'name_ar', 'name_en'])
            ->map(function ($d) use ($locale) {
                return [
                    'id' => (string)$d->id,
                    'name' => $locale == 'ar' ? $d->name_ar : $d->name_en
                ];
            })->toArray();
    }

    public function getAvailabilityStatusAttribute($value)
    {
        // If marked as busy, verify if there is an actual in_progress order
        if ($value === 'busy') {
            $hasActiveTask = $this->orders()->where('status', 'in_progress')->exists();
            if (!$hasActiveTask) {
                return 'available';
            }
        }
        return $value ?? 'available';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function maintenanceCompany()
    {
        return $this->belongsTo(MaintenanceCompany::class);
    }

    public function category()
    {
        return $this->belongsTo(Service::class, 'category_id');
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

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules()
    {
        return $this->hasMany(TechnicianSchedule::class);
    }
}
