<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = ['city_id', 'name_ar', 'name_en'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
