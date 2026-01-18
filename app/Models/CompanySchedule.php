<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_company_id',
        'day',
        'start_time',
        'end_time',
    ];

    public function maintenanceCompany()
    {
        return $this->belongsTo(MaintenanceCompany::class);
    }
}
