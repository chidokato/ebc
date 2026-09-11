<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $fillable = ['title', 'booking_email', 'logo_path', 'white_logo_path', 'favicon_path', 'social_image_path', 'seo_title', 'seo_description', 'seo_keywords', 'head_code', 'footer_code'];

    public function getSocialImageUrlAttribute(): string
    {
        return $this->social_image_path
            ? asset($this->social_image_path)
            : 'https://elitebusinesscenter.vn/uploads/homepage-sections/images/07774b45-954e-490c-a9c8-a4338adea1c0.jpg';
    }

    public static function current(): self
    {
        return static::find(1) ?? new static(['title' => 'Elite Business Center']);
    }
}
