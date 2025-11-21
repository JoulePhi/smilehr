<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'link_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getImagePathAttribute(): string
    {
        return asset('storage/' . $this->attributes['image_path']);
    }
}
