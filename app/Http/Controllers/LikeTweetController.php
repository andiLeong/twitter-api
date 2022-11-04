<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use Illuminate\Http\Request;

class LikeTweetController extends Controller
{
    public function store(Tweet $tweet)
    {
        $tweet->likeBy(auth()->user());
    }
}
