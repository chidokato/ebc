<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $fillable = ['title', 'booking_email', 'logo_path', 'white_logo_path', 'favicon_path', 'seo_title', 'seo_description', 'seo_keywords', 'head_code', 'footer_code'];

    public static function current(): self
    {
        return static::find(1) ?? new static(['title' => 'Elite Business Center']);
    }
}
