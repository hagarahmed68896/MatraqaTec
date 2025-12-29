<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'image',
        'button_text_ar',
        'button_text_en',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];
}
