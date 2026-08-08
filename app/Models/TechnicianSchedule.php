<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_id',
        'day',
        'start_time',
        'end_time',
    ];

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }
}
