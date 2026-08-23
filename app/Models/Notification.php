<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    const TYPES = ['alert', 'reminder', 'notification'];
    const TARGET_AUDIENCES = ['clients', 'companies', 'technicians', 'all', 'individual_user'];
    const STATUSES = ['sent', 'scheduled', 'unsent'];

    // Specific Notification Type Slugs for Data Logic
    const TYPE_NEW_ORDER = 'new_order';
    const TYPE_ORDER_ACCEPTED = 'order_accepted';
    const TYPE_ORDER_REJECTED = 'order_rejected';
    const TYPE_ORDER_CANCELLED = 'order_cancelled';
    const TYPE_ORDER_RESCHEDULED = 'order_rescheduled';
    const TYPE_WORK_STARTED = 'work_started';
    const TYPE_WORK_FINISHED = 'work_finished';
    const TYPE_STATUS_UPDATE = 'status_update';
    const TYPE_SUB_STATUS_UPDATE = 'sub_status_update';
    const TYPE_TECHNICIAN_ASSIGNED = 'technician_assigned';
    const TYPE_TECHNICIAN_ARRIVED = 'technician_arrived';
    const TYPE_TECHNICIAN_ON_WAY = 'technician_on_way';
    const TYPE_ORDER_PHOTOS = 'order_photos';

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

    public function userActions()
    {
        return $this->hasMany(UserNotificationAction::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    public function getUrlAttribute()
    {
        if (empty($this->data)) {
            return null;
        }

        try {
            if (isset($this->data['order_id'])) {
                return route('admin.orders.show', $this->data['order_id']);
            }
            if (isset($this->data['request_id'])) {
                return route('admin.technician-requests.show', $this->data['request_id']);
            }
            if (isset($this->data['complaint_id'])) {
                return route('admin.complaints.show', $this->data['complaint_id']);
            }
            if (isset($this->data['inquiry_id'])) {
                return route('admin.inquiries.show', $this->data['inquiry_id']);
            }
            if (isset($this->data['contract_id'])) {
                return route('admin.contracts.show', $this->data['contract_id']);
            }
            if (isset($this->data['technician_id'])) {
                return route('admin.technicians.show', $this->data['technician_id']);
            }
            if (isset($this->data['user_id'])) {
                return route('admin.users.show', $this->data['user_id']);
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}

