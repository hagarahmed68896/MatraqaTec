<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    const TYPES = ['alert', 'reminder', 'notification'];
    const TARGET_AUDIENCES = ['clients', 'companies', 'technicians', 'all', 'individual_user', 'admin'];
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
    const TYPE_ORDER_ACCEPTED_WAITING_PAYMENT = 'order_accepted_waiting_payment';
    const TYPE_ORDER_PAID = 'order_paid';
    const TYPE_PAYMENT_SUCCESSFUL = 'payment_successful';

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

    /**
     * Scope to retrieve ONLY incoming system notifications intended for admin
     * (e.g. new orders, cancellations, complaints, join requests, inquiries)
     * and strictly EXCLUDE outgoing broadcast marketing notifications sent by admin to users.
     */
    public function scopeForAdmin($query, $userId = null)
    {
        $userId = $userId ?? \Illuminate\Support\Facades\Auth::id();

        return $query->where(function ($q) {
            // Must be targeted to admin OR have incoming system notification type/data
            $q->where('target_audience', 'admin')
              ->orWhereIn('type', [
                  'new_order',
                  'order_cancelled',
                  'order_rescheduled',
                  'reschedule_requested',
                  'technician_request',
                  'complaint',
                  'new_complaint',
                  'inquiry',
                  'contact_us',
                  'additional_visit',
                  'stuck_order_expired',
                  'new_customer',
                  'new_technician',
                  'new_company',
              ])
              ->orWhere(function ($sub) {
                  $sub->whereNotNull('data')
                      ->where(function ($d) {
                          $d->where('data->type', 'new_order')
                            ->orWhere('data->type', 'technician_request')
                            ->orWhere('data->type', 'complaint')
                            ->orWhere('data->type', 'inquiry')
                            ->orWhere('data->type', 'order_cancelled')
                            ->orWhere('data->type', 'order_rescheduled')
                            ->orWhereNotNull('data->order_id')
                            ->orWhereNotNull('data->request_id')
                            ->orWhereNotNull('data->complaint_id')
                            ->orWhereNotNull('data->inquiry_id');
                      });
              });
        })
        // Filter by user_id if specific admin or null for global admin alerts
        ->where(function ($q) use ($userId) {
            if ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereNull('user_id');
            }
        })
        // Strictly exclude broadcast marketing notifications sent to clients, companies, technicians, or 'all'
        ->whereNotIn('target_audience', ['clients', 'companies', 'technicians', 'all']);
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

    /**
     * Send notification to all admin users and persist to database for dashboard
     */
    public static function notifyAdmins(array $details)
    {
        try {
            $admins = User::whereIn('type', ['admin', 'supervisor'])->get();
            if ($admins->isEmpty()) {
                self::create([
                    'user_id' => null,
                    'type' => $details['type'] ?? 'notification',
                    'target_audience' => 'admin',
                    'title_ar' => $details['title_ar'] ?? '',
                    'title_en' => $details['title_en'] ?? '',
                    'body_ar' => $details['body_ar'] ?? '',
                    'body_en' => $details['body_en'] ?? '',
                    'data' => $details['data'] ?? [],
                    'status' => 'sent',
                    'is_read' => false,
                ]);
            } else {
                foreach ($admins as $admin) {
                    self::create([
                        'user_id' => $admin->id,
                        'type' => $details['type'] ?? 'notification',
                        'target_audience' => 'admin',
                        'title_ar' => $details['title_ar'] ?? '',
                        'title_en' => $details['title_en'] ?? '',
                        'body_ar' => $details['body_ar'] ?? '',
                        'body_en' => $details['body_en'] ?? '',
                        'data' => $details['data'] ?? [],
                        'status' => 'sent',
                        'is_read' => false,
                    ]);

                    if ($admin->fcm_token && $admin->notification_enabled) {
                        try {
                            $admin->sendFcmNotification($details, false);
                        } catch (\Exception $fcmEx) {
                            \Illuminate\Support\Facades\Log::error("FCM Admin error: " . $fcmEx->getMessage());
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("NotifyAdmins error: " . $e->getMessage());
        }
    }
}

