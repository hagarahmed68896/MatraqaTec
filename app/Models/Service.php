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
    ];

    protected $appends = ['image_url', 'icon_url'];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset($this->image) : null;
    }

    public function getIconUrlAttribute()
    {
        return $this->icon ? asset($this->icon) : null;
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
}
