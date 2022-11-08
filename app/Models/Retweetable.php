<?php

namespace App\Models;

class Retweetable
{
    private Tweet $tweet;
    private User $user;
    private $body;

    public function __construct(Tweet $tweet, User $user, $body)
    {
        $this->tweet = $tweet;
        $this->user = $user;
        $this->body = $body;
    }

    public function retweeted()
    {
        return $this->tweet
            ->retweets()
            ->where('user_id', $this->user->id)
            ->exists();
    }

    public function retweet()
    {
        if ($this->retweeted()) {
            return $this->unRetweet();
        }

        if ($this->tweet->isRetweeted()) {
            abort(403, 'This Tweet is retweeted cant be retweet again');
        }

        return tap(
            $this->user->tweet($this->body, $this->tweet->id),
            fn() => $this->tweet->retweets()->attach($this->user),
        );
    }

    public function unRetweet()
    {
        $this->tweet->retweets()->detach($this->user);
        Tweet::where('user_id', $this->user->id)
            ->where('retweeted_id', $this->tweet->id)
            ->delete();
        return true;
    }
}
