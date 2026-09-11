<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopupSetting extends Model
{
    protected $fillable = ['is_enabled', 'title', 'content', 'offer_content', 'image_path', 'offer_image_path', 'launch_label', 'submit_label'];

    protected $casts = ['is_enabled' => 'boolean'];

    protected $attributes = [
        'is_enabled' => true,
        'title' => 'Nhận ƯU ĐÃI',
        'content' => 'HỘI TRƯỜNG SỰ KIỆN & PHÒNG HỘI THẢO',
        'offer_content' => "Giảm\nÁP DỤNG TRONG KHUNG GIỜ\n✦ 07:00 – 22:00\n✦ 07:00 – 22:00",
        'launch_label' => 'Nhận ưu đãi 50%',
        'submit_label' => 'ĐẶT LỊCH NGAY',
    ];

    public static function current(): self
    {
        return static::find(1) ?? new static;
    }

    public function getImageUrlAttribute(): string
    {
        return asset($this->image_path ?: 'frontend/img/popup.jpg');
    }

    public function getOfferImageUrlAttribute(): string
    {
        return asset($this->offer_image_path ?: 'frontend/img/50.png');
    }
}
