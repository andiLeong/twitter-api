<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|min:3|max:20',
            'username' => 'required|min:3|max:20|unique:users',
            'password' => 'required|confirmed',
            'device_name' => 'required',
        ]);

        $user = User::create(Arr::except($validated, ['device_name']));

        return response()->json([
            'user' => $user,
            'token' => $user->createToken($validated['device_name'])
                ->plainTextToken,
        ]);
    }
}
