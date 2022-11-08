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

    public function isRetweeted()
    {
        return $this->retweeted_id !== null;
    }

    public function retweet(User $user, $body)
    {
        return (new Retweetable($this, $user, $body))->retweet();
    }
}
