<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'discount',
        'payment_method',
        'scheduled_at',
        'original_scheduled_at',
        'address',
        'notes',
        'rejection_reason',
        'spare_parts_metadata',
        'client_signature',
        'assigned_at',
        'latitude',
        'longitude',
        'proposed_scheduled_at',
        'reschedule_requested_by',
        'rejected_by_user_id',
        'is_immediate',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'original_scheduled_at' => 'datetime',
        'assigned_at' => 'datetime',
        'proposed_scheduled_at' => 'datetime',
        'spare_parts_metadata' => 'array',
        'is_immediate' => 'boolean',
        'discount' => 'decimal:2',
    ];

    protected $appends = ['status_label', 'status_color', 'formatted_scheduled_date', 'formatted_scheduled_time', 'formatted_proposed_scheduled_date', 'formatted_proposed_scheduled_time', 'client_name', 'client_phone', 'technician_name', 'technician_phone', 'technician_avatar', 'acceptance_duration_minutes', 'acceptance_expiry_time', 'remaining_acceptance_seconds', 'client_signature_url', 'attachment_urls', 'image', 'can_reschedule', 'is_paid', 'required_photos_before_count', 'required_photos_after_count', 'subtotal_before_vat', 'vat_amount', 'discount_amount', 'original_price_before_tax'];

    protected static function booted()
    {
        static::saved(function ($order) {
            if ($order->status === 'completed' && $order->total_price > 0) {
                $hasCompletedPayment = $order->payments()->where('status', 'completed')->exists();
                if ($hasCompletedPayment) {
                    $invoiceNumber = 'INV-' . $order->order_number;
                    $total = $order->total_price;
                    $amount = round($total / 1.15, 2);
                    $tax = round($total - $amount, 2);

                    \App\Models\Invoice::updateOrCreate(
                        ['order_id' => $order->id],
                        [
                            'invoice_number' => $invoiceNumber,
                            'issue_date' => now(),
                            'status' => 'not_sent',
                            'amount' => $amount,
                            'tax' => $tax,
                            'total' => $total,
                        ]
                    );
                }
            }
        });
    }

    public function getSubtotalBeforeVatAttribute()
    {
        $vatRate = (float) \App\Models\Setting::getByKey('vat_rate', 15);
        $divisor = 1 + ($vatRate / 100);
        $total = (float) ($this->total_price ?? 0);
        return round($total / $divisor, 2);
    }

    public function getVatAmountAttribute()
    {
        $total = (float) ($this->total_price ?? 0);
        return round($total - $this->subtotal_before_vat, 2);
    }

    public function getDiscountAmountAttribute()
    {
        return (float) ($this->discount ?? 0);
    }

    public function getOriginalPriceBeforeTaxAttribute()
    {
        return round($this->subtotal_before_vat + $this->discount_amount, 2);
    }

    public function getClientSignatureUrlAttribute()
    {
        if (!$this->client_signature) return null;
        if (Str::startsWith($this->client_signature, 'data:image')) {
            return $this->client_signature;
        }
        // Since we now save directly to public/, asset() will link to it correctly
        return asset($this->client_signature);
    }

    public function getIsPaidAttribute()
    {
        $totalPaid = (float) $this->payments()->where('status', 'completed')->sum('amount');
        $totalRequired = (float) $this->total_price;
        
        if ($totalRequired <= 0) return false;

        // Allow 1.0 SAR tolerance for small differences
        $tolerance = 1.0; 
        return (round($totalPaid, 2) >= round($totalRequired - $tolerance, 2));
    }

    public function getCanRescheduleAttribute()
    {
        $user = auth()->user();
        if (!$user) return false;

        // Common status check: only scheduled/accepted/new orders can be rescheduled
        if (!in_array($this->status, ['new', 'scheduled', 'accepted'])) {
            return false;
        }

        // Check if there's already a pending reschedule request
        if ($this->sub_status === 'reschedule_requested') {
            return false;
        }

        // Must not have started work (sub_status must be empty)
        if ($this->sub_status) {
            return false;
        }

        // Must only allow rescheduling after the scheduled time has passed
        if ($this->scheduled_at && $this->scheduled_at->isFuture()) {
            return false;
        }

        // Removed time restrictions for technicians. 
        // Since we now use a Request & Approval system, anyone can *request* a reschedule at any time, 
        // and the other party can simply accept or reject it.
        return true;
    }

    public function getAttachmentUrlsAttribute()
    {
        return $this->attachments->map(function ($attachment) {
            return $attachment->file_path ? asset($attachment->file_path) : null;
        })->filter()->values();
    }

    public function getImageAttribute()
    {
        return $this->attachment_urls[0] ?? null;
    }

    public function getRequiredPhotosBeforeCountAttribute()
    {
        return (int) \App\Models\Setting::getByKey('required_photos_before_count', 1);
    }

    public function getRequiredPhotosAfterCountAttribute()
    {
        return (int) \App\Models\Setting::getByKey('required_photos_after_count', 1);
    }

    public function getAcceptanceDurationMinutesAttribute()
    {
        $durationKey = $this->is_immediate ? 'immediate_order_acceptance_duration' : 'order_acceptance_duration';
        $defaultDuration = $this->is_immediate ? 5 : 15;
        $duration = (int) \App\Models\Setting::getByKey($durationKey, $defaultDuration);
        
        if ($this->assigned_at) {
            $expiryTime = $this->assigned_at->copy()->addMinutes($duration);
            $remaining = now()->diffInMinutes($expiryTime, false);
            return $remaining > 0 ? (int) $remaining : 0;
        }
        return $duration;
    }

    public function getRemainingAcceptanceSecondsAttribute()
    {
        $durationKey = $this->is_immediate ? 'immediate_order_acceptance_duration' : 'order_acceptance_duration';
        $defaultDuration = $this->is_immediate ? 5 : 15;
        $duration = (int) \App\Models\Setting::getByKey($durationKey, $defaultDuration);
        
        if ($this->assigned_at) {
            $expiryTime = $this->assigned_at->copy()->addMinutes($duration);
            $remainingSeconds = now()->diffInSeconds($expiryTime, false);
            return $remainingSeconds > 0 ? (int) $remainingSeconds : 0;
        }
        return $duration * 60;
    }

    public function getAcceptanceExpiryTimeAttribute()
    {
        if (!$this->assigned_at) return null;
        $durationKey = $this->is_immediate ? 'immediate_order_acceptance_duration' : 'order_acceptance_duration';
        $defaultDuration = $this->is_immediate ? 5 : 15;
        $duration = (int) \App\Models\Setting::getByKey($durationKey, $defaultDuration);
        
        return $this->assigned_at->copy()->addMinutes($duration);
    }

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
        if (is_array($this->technician)) {
            return $this->technician['name'] ?? '';
        }
        return $this->technician->user->name ?? ($this->technician->name ?? '');
    }

    public function getTechnicianPhoneAttribute()
    {
        $tech = $this->technician;
        if (!$tech) return '';
        
        if (is_array($tech)) {
            return $tech['phone'] ?? ($tech['user']['phone'] ?? '');
        }
        
        return $tech->user->phone ?? ($tech->phone ?? '');
    }

    public function getTechnicianAvatarAttribute()
    {
        if (is_array($this->technician)) {
            return $this->technician['image'] ?? null;
        }
        if (!$this->technician || !$this->technician->user) return null;
        return $this->technician->user->avatar_url;
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

    public function getFormattedProposedScheduledDateAttribute()
    {
        return $this->proposed_scheduled_at ? $this->proposed_scheduled_at->format('Y/m/d') : null;
    }

    public function getFormattedProposedScheduledTimeAttribute()
    {
        if (!$this->proposed_scheduled_at) return null;
        return $this->proposed_scheduled_at->translatedFormat('h:i a');
    }

    /**
     * Bulk update overdue orders to 'expired' sub_status
     */
    public static function syncExpiredOrders()
    {
        // 1. Clean up expired technician assignments first
        $durationScheduled = (int) \App\Models\Setting::getByKey('order_acceptance_duration', 15);
        $durationImmediate = (int) \App\Models\Setting::getByKey('immediate_order_acceptance_duration', 5);
        
        // --- 1.1 CLEAN UP IMMEDIATE ORDERS (TIMEOUT = 5 MINUTES) ---
        // Independent immediate orders
        self::where('is_immediate', true)
            ->whereNotNull('technician_id')
            ->whereNull('maintenance_company_id')
            ->where(function($q) {
                $q->where('status', 'scheduled')
                  ->orWhere('status', 'new');
            })
            ->where('assigned_at', '<', now()->subMinutes($durationImmediate))
            ->update([
                'technician_id' => null,
                'status' => 'new',
                'assigned_at' => null,
                'sub_status' => null,
            ]);

        // Company immediate orders
        self::where('is_immediate', true)
            ->whereNotNull('technician_id')
            ->whereNotNull('maintenance_company_id')
            ->where('sub_status', 'pending_tech_acceptance')
            ->where('assigned_at', '<', now()->subMinutes($durationImmediate))
            ->update([
                'technician_id' => null,
                'assigned_at' => null,
                'sub_status' => null,
            ]);

        // --- 1.2 CLEAN UP SCHEDULED ORDERS (TIMEOUT = 15 MINUTES) ---
        // Independent scheduled orders
        self::where('is_immediate', false)
            ->whereNotNull('technician_id')
            ->whereNull('maintenance_company_id')
            ->where(function($q) {
                $q->where('status', 'scheduled')
                  ->orWhere('status', 'new');
            })
            ->where('assigned_at', '<', now()->subMinutes($durationScheduled))
            ->update([
                'technician_id' => null,
                'status' => 'new',
                'assigned_at' => null,
                'sub_status' => null,
            ]);

        // Company scheduled orders
        self::where('is_immediate', false)
            ->whereNotNull('technician_id')
            ->whereNotNull('maintenance_company_id')
            ->where('sub_status', 'pending_tech_acceptance')
            ->where('assigned_at', '<', now()->subMinutes($durationScheduled))
            ->update([
                'technician_id' => null,
                'assigned_at' => null,
                'sub_status' => null,
            ]);

        // 2. Mark overdue orders as expired
        return self::whereIn('status', ['new', 'scheduled', 'accepted'])
            ->where('scheduled_at', '<', now())
            ->where('status', '!=', 'expired')
            ->whereNotIn('sub_status', ['reschedule_requested', 'additional_visit', 'pending_tech_acceptance'])
            ->update([
                'status' => 'expired',
                'sub_status' => 'expired'
            ]);
    }


    public function getStatusLabelAttribute()
    {
        $mainLabels = [
            'new' => 'جديد',
            'accepted' => 'مقبول',
            'scheduled' => 'مجدولة',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتملة',
            'rejected' => 'مرفوضة',
            'cancelled' => 'ملغية',
            'expired' => 'منتهية / متأخرة',
        ];

        // Handle expired sub-status or status for overdue appointments
        if ($this->sub_status === 'expired' || $this->status === 'expired') {
            return 'يحتاج لإعادة جدولة';
        }

        // Sub-status label for pending tech acceptance
        if ($this->sub_status === 'pending_tech_acceptance') {
            return 'جديد';
        }

        // Sub-status for additional visit request
        if ($this->sub_status === 'additional_visit') {
            return 'في انتظار تأكيد العميل (زيارة إضافية)';
        }

        // Sub-status for general reschedule request
        if ($this->sub_status === 'reschedule_requested') {
            return 'في انتظار تأكيد الموعد الجديد';
        }

        // Sub-status labels for "in_progress" state
        if ($this->status === 'in_progress' && $this->sub_status) {
            $subLabels = [
                'on_way' => 'في الطريق',
                'arrived' => 'وصل',
                'work_started' => 'بدأ العمل',
            ];
            
            return $subLabels[$this->sub_status] ?? $mainLabels['in_progress'];
        }

        return $mainLabels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        if ($this->sub_status === 'expired') {
            return '#FF8C00'; // DarkOrange
        }

        if (in_array($this->sub_status, ['pending_tech_acceptance', 'additional_visit', 'reschedule_requested'])) {
            return '#FF9800'; // Amber - awaiting action/confirmation
        }

        $colors = [
            'new' => '#FFA500', // Orange
            'accepted' => '#4CAF50', // Green
            'scheduled' => '#2196F3', // Blue
            'in_progress' => '#2196F3', // Blue
            'completed' => '#4CAF50', // Green
            'rejected' => '#F44336', // Red
            'cancelled' => '#9E9E9E', // Grey
            'expired' => '#FF8C00', // DarkOrange
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

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
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
