<?php

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::get('tweets', function (Request $request) {
    $page = $request->get('page', 1);

    $requestedRecords = 10 * $page;
    $query = Tweet::query()
        ->with('user:id,avatar,username,name')
        ->latest('id');

    $total = $query->count();
    if($requestedRecords > $total){
       return [];
    }

    return $query
        ->limit($requestedRecords)
        ->get();
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
