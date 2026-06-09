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
