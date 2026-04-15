<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'image',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'button_text_ar',
        'button_text_en',
    ];

    protected $appends = ['full_image_url'];

    public function getFullImageUrlAttribute()
    {
        if (!$this->image) return null;
        
        // If it starts with 'uploads/', it's in the public folder
        if (str_starts_with($this->image, 'uploads/')) {
            return asset($this->image);
        }

        // Support for some legacy naked paths if they still exist in some tables
        return asset('uploads/' . $this->image);
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
