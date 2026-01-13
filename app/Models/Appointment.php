<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'technician_id',
        'appointment_date',
        'status',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
    ];

    protected $appends = ['status_label', 'status_color'];

    public function getStatusLabelAttribute()
    {
        $labels = [
            'scheduled' => 'مجدولة',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغية',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'scheduled' => '#2196F3', // Blue
            'in_progress' => '#2196F3', // Blue
            'completed' => '#4CAF50', // Green
            'cancelled' => '#9E9E9E', // Grey
        ];

        return $colors[$this->status] ?? '#000000';
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }
}
