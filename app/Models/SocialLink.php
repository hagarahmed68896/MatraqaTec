<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobile',
        'email',
        'facebook',
        'instagram',
        'twitter',
        'linkedin',
        'tiktok',
        'platform_url',
    ];
}
