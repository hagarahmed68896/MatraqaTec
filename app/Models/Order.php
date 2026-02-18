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
        'assigned_at',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'assigned_at' => 'datetime',
        'spare_parts_metadata' => 'array',
    ];

    protected $appends = ['status_label', 'status_color', 'formatted_scheduled_date', 'formatted_scheduled_time', 'client_name', 'client_phone', 'technician_name', 'technician_avatar'];

    public function getClientNameAttribute()
    {
        return $this->user->name ?? 'عميل';
    }

    public function getClientPhoneAttribute()
    {
        return $this->user->phone ?? '';
    }

    public function getTechnicianNameAttribute()
    {
        return $this->technician->user->name ?? ($this->technician->name ?? '');
    }

    public function getTechnicianAvatarAttribute()
    {
        if (!$this->technician || !$this->technician->user) return null;
        return $this->technician->user->avatar ? asset('storage/' . $this->technician->user->avatar) : null;
    }

    public function getFormattedScheduledDateAttribute()
    {
        return $this->scheduled_at ? $this->scheduled_at->format('Y/m/d') : null;
    }

    public function getFormattedScheduledTimeAttribute()
    {
        if (!$this->scheduled_at) return null;
        return $this->scheduled_at->translatedFormat('h:i a');
    }

    public function getStatusLabelAttribute()
    {
        $mainLabels = [
            'new' => 'قيد المراجعة',
            'accepted' => 'مقبول',
            'scheduled' => 'مجدولة',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتملة',
            'rejected' => 'مرفوضة',
            'cancelled' => 'ملغية',
        ];

        // Sub-status labels for "in_progress" state
        if ($this->status === 'in_progress' && $this->sub_status) {
            $subLabels = [
                'on_way' => 'في الطريق',
                'arrived' => 'وصل',
                'work_started' => 'بدأ العمل',
                'additional_visit' => 'زيارة إضافية',
            ];
            
            return $subLabels[$this->sub_status] ?? $mainLabels['in_progress'];
        }

        return $mainLabels[$this->status] ?? $this->status;
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
