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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
