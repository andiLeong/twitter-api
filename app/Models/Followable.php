<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

trait Followable
{

    /**
     * @param User $user
     * @return mixed
     */
    public function isFollowing(User $user)
    {
        return $this->follow->contains(fn($following) => $user->id === $following->id);
    }

    public function beingFollow()
    {
        return $this->belongsToMany(User::class, 'follows', 'follow_user_id', 'user_id')->withTimestamps();
    }

    public function follow()
    {
        return $this->belongsToMany(User::class, 'follows', 'user_id', 'follow_user_id')->withTimestamps();
    }

    public function follows(User|Collection $userToFollow)
    {
        return $this->follow()->toggle($userToFollow);
    }
}
