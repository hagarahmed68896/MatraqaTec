<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'photo',
        'company_name',
        'service_id',
        'years_experience',
        'status',
        'rejection_reason',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
