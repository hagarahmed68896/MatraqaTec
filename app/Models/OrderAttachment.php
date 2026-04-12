<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'file_path',
        'type',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        if (!$this->file_path) return null;
        return asset($this->file_path);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
