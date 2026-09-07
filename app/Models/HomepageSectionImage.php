<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSectionImage extends Model
{
    use HasFactory;

    protected $fillable = ['homepage_section_id', 'path', 'sort_order'];

    protected $casts = ['sort_order' => 'integer', 'homepage_section_id' => 'integer'];

    public function section()
    {
        return $this->belongsTo(HomepageSection::class, 'homepage_section_id');
    }
}
