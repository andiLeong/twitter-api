<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeTweetController;
use App\Http\Controllers\TweetController;
use App\Http\Controllers\UserController;
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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user/{id}', [UserController::class, 'show']);
    Route::post('follow-toggle/{user}', [FollowController::class, 'store']);
    Route::post('like-tweet-toggle/{tweet}', [
        LikeTweetController::class,
        'store',
    ]);
    Route::post(
        'logout',
        fn() => auth()
            ->user()
            ->tokens()
            ->delete(),
    );
    Route::get('tweets', [TweetController::class, 'index']);
    Route::post('tweets', [TweetController::class, 'store']);
    Route::delete('tweets/{tweet}', [TweetController::class, 'destroy']);
});

Route::get('tweets/{id}', [TweetController::class, 'show']);

Route::post('login', [LoginController::class, 'store']);
Route::post('register', [RegisterController::class, 'store']);
