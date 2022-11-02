<?php

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('logout', fn() => auth()->user()->tokens()->delete());

});

Route::get('tweets', function () {
    return Tweet::query()
        ->with('user:id,avatar,username,name')
        ->latest('id')
        ->paginate(10);
});

Route::get('tweets/{tweet}', function (Tweet $tweet) {
    return $tweet->load('user:id,avatar,username,name');
});

Route::post('tweets', function (Request $request) {
    $request->validate([
        'body' => 'required|max:200'
    ]);

    return Tweet::create([
        'body' => $request->body,
        'user_id' => User::pluck('id')->random()
    ]);
});

Route::get('user/{id}', function ($id) {
    return User::with('tweets')->findOrFail($id);
});


Route::post('login', function (Request $request) {

    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token = $user->createToken($request->device_name)->plainTextToken;
    return [
        'user' => $user->only(['id', 'username', 'name', 'avatar', 'email']),
        'token' => $token,
    ];

});

Route::post('register', function (Request $request) {

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
    ]);

});
