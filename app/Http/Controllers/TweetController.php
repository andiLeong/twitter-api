<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use Illuminate\Http\Request;

class TweetController extends Controller
{
    public function index()
    {
        $usersIds = auth()
            ->user()
            ->follow->pluck('id')
            ->merge([auth()->id()])
            ->all();

        $res = tap(
            Tweet::query()
                ->whereIn('user_id', $usersIds)
                ->withCount(['likes', 'retweets'])
                ->with(
                    'user:id,avatar,username,name',
                    'likes',
                    'retweetedTweet:id,user_id,body',
                    'retweetedTweet.user:id,name,avatar',
                )
                ->latest('id')
                ->paginate(10),
        )->each(function ($tweet) {
            $tweet->retweeted_by_user = $tweet->retweetedBy();
        });

        return $res;
    }

    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required|max:200',
        ]);

        return auth()
            ->user()
            ->tweet($request->body);
    }

    public function show($id)
    {
        return Tweet::with('user:id,avatar,username,name')
            ->withCount('likes')
            ->where('id', $id)
            ->firstOrFail();
    }

    public function destroy(Tweet $tweet)
    {
        if (auth()->id() !== $tweet->user_id) {
            abort(403, 'Your dnt have permission');
        }

        $tweet->delete();
    }
}
