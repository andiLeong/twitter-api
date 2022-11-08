<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use HasFactory, Likable;

    protected $appends = ['liked_by_user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function retweets()
    {
        return $this->belongsToMany(
            User::class,
            'retweets',
            'tweet_id',
            'user_id',
        )->withTimestamps();
    }

    public function retweetedTweet()
    {
        return $this->hasOne(Tweet::class, 'id', 'retweeted_id');
    }

    public function isRetweeted()
    {
        return $this->retweeted_id !== null;
    }

    public function retweetedBy(User $user = null)
    {
        $user ??= auth()->user();
        return $this->retweets->contains(fn($t) => $t->id === $user->id);
    }

    public function retweet(User $user, $body)
    {
        return (new Retweetable($this, $user, $body))->retweet();
    }
}
