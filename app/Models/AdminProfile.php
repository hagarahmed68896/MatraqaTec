<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Assuming User model is in the same namespace

class AdminProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name_ar',
        'last_name_ar',
        'first_name_en',
        'last_name_en',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
