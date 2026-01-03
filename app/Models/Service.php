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
    ];

    public function parent()
    {
        return $this->belongsTo(Service::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Service::class, 'parent_id');
    }

    public function technicians()
    {
        return $this->hasMany(Technician::class);
    }
}
