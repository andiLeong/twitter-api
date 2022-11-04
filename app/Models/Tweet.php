<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use HasFactory, Likable;

    protected $appends = ['liked_by_user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLikedByUserAttribute()
    {
        if (auth()->check()) {
            return $this->likedBy();
        }
    }
}
