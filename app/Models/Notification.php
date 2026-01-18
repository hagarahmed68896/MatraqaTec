<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    const TYPES = ['alert', 'reminder', 'notification'];
    const TARGET_AUDIENCES = ['clients', 'companies', 'technicians', 'all'];
    const STATUSES = ['sent', 'scheduled', 'not_sent'];

    // Specific Notification Type Slugs for Data Logic
    const TYPE_NEW_ORDER = 'new_order';
    const TYPE_ORDER_ACCEPTED = 'order_accepted';
    const TYPE_ORDER_REJECTED = 'order_rejected';
    const TYPE_ORDER_RESCHEDULED = 'order_rescheduled';
    const TYPE_WORK_STARTED = 'work_started';
    const TYPE_WORK_FINISHED = 'work_finished';
    const TYPE_STATUS_UPDATE = 'status_update';
    const TYPE_SUB_STATUS_UPDATE = 'sub_status_update';
    const TYPE_TECHNICIAN_ASSIGNED = 'technician_assigned';

    protected $fillable = [
        'user_id',
        'is_read',
        'type',
        'title_ar',
        'title_en',
        'body_ar',
        'body_en',
        'data', // Metadata for actions (e.g. order_id for tracking)
        'target_audience',
        'status',
        'scheduled_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_read' => 'boolean',
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
