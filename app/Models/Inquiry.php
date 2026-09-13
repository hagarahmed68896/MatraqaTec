<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'account_type',
        'message',
        'reply',
        'status',
    ];

    protected static function booted()
    {
        static::created(function ($inquiry) {
            Notification::notifyAdmins([
                'type' => 'inquiry',
                'title_ar' => 'رسالة / استفسار جديد',
                'title_en' => 'New Inquiry / Message',
                'body_ar' => "استفسار جديد من {$inquiry->name} ({$inquiry->phone})",
                'body_en' => "New inquiry from {$inquiry->name} ({$inquiry->phone})",
                'data' => [
                    'type' => 'inquiry',
                    'inquiry_id' => $inquiry->id,
                    'name' => $inquiry->name,
                    'phone' => $inquiry->phone,
                ]
            ]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
