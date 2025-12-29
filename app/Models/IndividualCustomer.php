<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndividualCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name_ar',
        'first_name_en',
        'last_name_ar',
        'last_name_en',
        'address',
        'order_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
