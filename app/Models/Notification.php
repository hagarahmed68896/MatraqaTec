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

    protected $fillable = [
        'type',
        'title_ar',
        'title_en',
        'body_ar',
        'body_en',
        'target_audience',
        'status',
        'scheduled_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];
}
