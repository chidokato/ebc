<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsArticle extends Model
{
    public const LANGUAGES = ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文', 'ko' => '한국어'];

    protected $fillable = ['translation_group', 'locale', 'title', 'excerpt', 'content', 'content_is_html', 'image_path', 'published_at'];

    protected $casts = ['published_at' => 'datetime', 'content_is_html' => 'boolean'];

    public function renderedContent(): string
    {
        return $this->content_is_html
            ? \App\Support\NewsHtml::clean($this->content ?? '')
            : \App\Support\NewsHtml::plain($this->content ?? '');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
