<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public function songs()
    {
        return $this->belongsToMany(Song::class, 'category_song')->withTimestamps();
    }

    public function scopeSearch($query, $search = null)
    {
        if ($search) {
            return $query->where('name', 'LIKE', "%$search%");
        }

        return $query;
    }
}
