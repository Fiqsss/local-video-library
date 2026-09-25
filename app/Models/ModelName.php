<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ModelName extends Model
{
    protected $table = 'models';

    protected $fillable = ['name'];

    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'model_video');
    }
}
