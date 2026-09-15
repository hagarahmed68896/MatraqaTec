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

    protected $appends = ['photo_url', 'iqama_photo_url', 'services', 'district_list', 'districts_formatted'];

    public function getDistrictListAttribute()
    {
        $districts = $this->districts;
        if (!$districts) {
            return collect();
        }

        if (is_string($districts)) {
            $decoded = json_decode($districts, true);
            $districts = is_array($decoded) ? $decoded : explode(',', $districts);
        }

        if (!is_array($districts) || empty($districts)) {
            return collect();
        }

        $ids = array_filter($districts, fn($v) => is_numeric($v));
        if (!empty($ids)) {
            return \App\Models\District::whereIn('id', $ids)->get();
        }

        return collect($districts)->map(function ($name) {
            return (object) ['name_ar' => $name, 'name_en' => $name];
        });
    }

    public function getDistrictsFormattedAttribute()
    {
        $districtModels = $this->district_list;
        if ($districtModels->isEmpty()) {
            return __('All Areas');
        }

        $locale = app()->getLocale();
        return $districtModels->map(function ($d) use ($locale) {
            return $locale === 'ar' ? ($d->name_ar ?? $d->name_en) : ($d->name_en ?? $d->name_ar);
        })->join('، ');
    }

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
        if (!$this->photo) {
            return null;
        }

        if (str_starts_with($this->photo, 'http://') || str_starts_with($this->photo, 'https://')) {
            return $this->photo;
        }

        $cleanPath = ltrim($this->photo, '/');

        if (str_starts_with($cleanPath, 'uploads/')) {
            return asset($cleanPath);
        }

        if (str_starts_with($cleanPath, 'storage/')) {
            return asset($cleanPath);
        }

        if (str_starts_with($cleanPath, 'public/')) {
            $stripped = substr($cleanPath, 7);
            if (file_exists(public_path('uploads/' . $stripped))) {
                return asset('uploads/' . $stripped);
            }
            return asset('storage/' . $stripped);
        }

        if (file_exists(public_path('uploads/' . $cleanPath))) {
            return asset('uploads/' . $cleanPath);
        }

        if (file_exists(public_path('uploads/technician_requests/photos/' . basename($cleanPath)))) {
            return asset('uploads/technician_requests/photos/' . basename($cleanPath));
        }

        if (file_exists(public_path('storage/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        return asset('uploads/' . $cleanPath);
    }

    public function getIqamaPhotoUrlAttribute()
    {
        if (!$this->iqama_photo) {
            return null;
        }

        if (str_starts_with($this->iqama_photo, 'http://') || str_starts_with($this->iqama_photo, 'https://')) {
            return $this->iqama_photo;
        }

        $cleanPath = ltrim($this->iqama_photo, '/');

        if (str_starts_with($cleanPath, 'uploads/')) {
            return asset($cleanPath);
        }

        if (str_starts_with($cleanPath, 'storage/')) {
            return asset($cleanPath);
        }

        if (str_starts_with($cleanPath, 'public/')) {
            $stripped = substr($cleanPath, 7);
            if (file_exists(public_path('uploads/' . $stripped))) {
                return asset('uploads/' . $stripped);
            }
            return asset('storage/' . $stripped);
        }

        if (file_exists(public_path('uploads/' . $cleanPath))) {
            return asset('uploads/' . $cleanPath);
        }

        if (file_exists(public_path('uploads/technician_requests/iqamas/' . basename($cleanPath)))) {
            return asset('uploads/technician_requests/iqamas/' . basename($cleanPath));
        }

        if (file_exists(public_path('uploads/technicians/ids/' . basename($cleanPath)))) {
            return asset('uploads/technicians/ids/' . basename($cleanPath));
        }

        if (file_exists(public_path('storage/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        return asset('uploads/' . $cleanPath);
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
