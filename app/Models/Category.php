<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'page_key'];

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function taggedVideos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'category_video');
    }
}
