<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function show($id)
    {
        return User::with('tweets')->withCount(['beingFollow','follow'])->findOrFail($id);
    }
}
