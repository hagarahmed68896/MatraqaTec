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
        
        // New format: bare filename saved in public/content_images/
        if (!str_contains($this->image, '/')) {
            return asset('content_images/' . $this->image);
        }
        
        // Old format: content_items/filename.jpg saved in storage
        return asset('storage/' . $this->image);
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
