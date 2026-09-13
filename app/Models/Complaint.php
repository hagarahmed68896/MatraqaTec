<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'order_id',
        'account_type',
        'phone',
        'type',
        'description',
        'attachment',
        'status',
    ];

    protected static function booted()
    {
        static::created(function ($complaint) {
            Notification::notifyAdmins([
                'type' => 'new_complaint',
                'title_ar' => 'شكوى جديدة',
                'title_en' => 'New Complaint',
                'body_ar' => "تم تقديم شكوى جديدة برقم #{$complaint->ticket_number}",
                'body_en' => "A new complaint #{$complaint->ticket_number} has been submitted",
                'data' => [
                    'type' => 'complaint',
                    'complaint_id' => $complaint->id,
                    'ticket_number' => $complaint->ticket_number,
                    'phone' => $complaint->phone,
                ]
            ]);
        });
    }

    protected $appends = ['attachment_url'];

    public function getAttachmentUrlAttribute()
    {
        if (!$this->attachment) {
            return null;
        }
        if (str_starts_with($this->attachment, 'http')) {
            return $this->attachment;
        }
        if (str_starts_with($this->attachment, 'uploads/')) {
            return asset($this->attachment);
        }
        return asset('storage/' . $this->attachment);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function actions()
    {
        return $this->hasMany(ComplaintAction::class)->orderBy('created_at', 'desc');
    }
}
