<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $guarded = [];

    protected $casts = [
        'brith_date' => 'date',
    ];

    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'artist_song')->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_song')->withTimestamps();
    }

    public function scopeSearch($query, $search = null)
    {
        if ($search) {
            return $query->where('title', 'LIKE', "%$search%");
        }

        return $query;
    }
}
