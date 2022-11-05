<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use Illuminate\Http\Request;

class TweetController extends Controller
{
    public function index()
    {
        return Tweet::query()
            ->withCount(['likes'])
            ->with('user:id,avatar,username,name', 'likes')
            ->latest('id')
            ->paginate(10);
    }

    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required|max:200',
        ]);

        return Tweet::create([
            'body' => $request->body,
            'user_id' => auth()->id(),
        ]);
    }

    public function show(Tweet $tweet)
    {
        return $tweet->load('user:id,avatar,username,name');
    }

    public function destroy(Tweet $tweet)
    {
        if (auth()->id() !== $tweet->user_id) {
            abort(403, 'Your dnt have permission');
        }

        $tweet->delete();
    }
}
