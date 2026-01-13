<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'city_id',
        'technician_id',
        'maintenance_company_id',
        'service_id',
        'status',
        'sub_status',
        'total_price',
        'payment_method',
        'scheduled_at',
        'address',
        'notes',
        'rejection_reason',
        'spare_parts_metadata',
        'client_signature',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'spare_parts_metadata' => 'array',
    ];

    protected $appends = ['status_label', 'status_color'];

    public function getStatusLabelAttribute()
    {
        $labels = [
            'new' => 'طلب جديد',
            'accepted' => 'مقبول',
            'scheduled' => 'مجدولة',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتملة',
            'rejected' => 'مرفوضة',
            'cancelled' => 'ملغية',
        ];

        // Specific logic for "On the way" or "Arrived" based on sub_status if needed
        if ($this->status === 'in_progress') {
            if ($this->sub_status === 'on_the_way') return 'في الطريق';
            if ($this->sub_status === 'arrived') return 'وصل';
        }

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'new' => '#FFA500', // Orange
            'accepted' => '#4CAF50', // Green
            'scheduled' => '#2196F3', // Blue
            'in_progress' => '#2196F3', // Blue
            'completed' => '#4CAF50', // Green
            'rejected' => '#F44336', // Red
            'cancelled' => '#9E9E9E', // Grey
        ];

        return $colors[$this->status] ?? '#000000';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    public function maintenanceCompany()
    {
        return $this->belongsTo(MaintenanceCompany::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function attachments()
    {
        return $this->hasMany(OrderAttachment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'order_services');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
