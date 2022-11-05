<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function store(User $user)
    {
        if (auth()->id() === $user->id) {
            return abort(403, 'You cant follow your self');
        }
        auth()
            ->user()
            ->follows($user);
    }
}
