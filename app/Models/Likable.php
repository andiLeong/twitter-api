<?php

namespace App\Models;

trait Likable
{

    public function likes()
    {
        return $this->belongsToMany(User::class, 'tweet_likes', 'tweet_id', 'user_id')->withTimestamps();
    }

    public function likeBy(User $user)
    {
        $this->likes()->toggle($user);
    }

    public function likedBy(User $user = null)
    {
        $user ??= auth()->user();
        return $this->likes->contains(fn($likes) => $user->id === $likes->id);
    }

    public function getLikedByUserAttribute()
    {
        if (auth()->check()) {
            return $this->likedBy();
        }
    }
}
