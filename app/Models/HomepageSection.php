<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    use HasFactory;

    protected $fillable = ['locale', 'parent_id', 'translation_group', 'key', 'title', 'sub_title', 'content', 'image_path', 'icon_path', 'link_url', 'link_label', 'sort_order', 'is_active', 'quick_items'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer', 'quick_items' => 'array'];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function images()
    {
        return $this->hasMany(HomepageSectionImage::class)->orderBy('sort_order');
    }
}
