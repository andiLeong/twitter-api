<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::with('tweets')
            ->withCount(['beingFollow', 'follow'])
            ->findOrFail($id);
        $user->follow_by_logged_in_user = auth()->check()
            ? auth()
                ->user()
                ->isFollowing($user)
            : null;
        return $user;
    }
}
