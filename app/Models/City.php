<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name_ar', 'name_en'];

    public function districts()
    {
        return $this->hasMany(District::class);
    }

    public function companies()
    {
        return $this->hasMany(MaintenanceCompany::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function services() // Services linked via city_id (if using specialized service per city logic)
    {
        return $this->hasMany(Service::class);
    }
}
