<?php

namespace Database\Seeders;

use App\Models\User;

class FollowUserSeed
{
    public $users;
    private int $limits;
    private array $userIds;

    public function __construct($limits = 20)
    {
        $this->users = User::limit($limits)->get();
        $this->limits = $limits;
        $this->userIds = range(1, $this->limits);
    }

    public function run()
    {
        $this->users->each(fn($user) => $this->follow($user));
    }

    public function follow($user)
    {
        $users = User::whereIn('id', $this->remainingIds($user))->get();
        $user->follows($users);
    }

    public function remainingIds($user)
    {
        return array_filter($this->userIds,
            fn($range) => $user->id !== $range
        );
    }

}
