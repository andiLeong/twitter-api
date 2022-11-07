<?php

namespace App\Http\Controllers;

use App\Models\Tweet;

class RetweetController extends Controller
{
    public function store(Tweet $tweet)
    {
        $data = request()->validate([
            'body' => 'nullable',
        ]);

        return auth()
            ->user()
            ->retweet($tweet, $data['body'] ?? null);
    }
}
