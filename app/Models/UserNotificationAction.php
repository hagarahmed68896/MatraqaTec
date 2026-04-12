<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationAction extends Model
{
    protected $fillable = [
        'user_id',
        'notification_id',
        'is_read',
        'is_deleted',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_deleted' => 'boolean',
    ];
}
