<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Followable;

    protected $appends = ['short_link'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (!isset($user->attributes['avatar'])) {
                $user->avatar = $user->generateAvatar();
            }
        });
    }

    public function tweets()
    {
        return $this->hasMany(Tweet::class);
    }

    public function retweets()
    {
        return $this->belongsToMany(
            Tweet::class,
            'retweets',
            'user_id',
            'tweet_id',
        )->withTimestamps();
    }

    public function getShortLinkAttribute()
    {
        if (!is_null($this->link)) {
            return substr(Str::mask($this->link, '.', '20'), 0, 23);
        }
    }

    public function password(): Attribute
    {
        return Attribute::make(null, set: fn($value) => Hash::make($value));
    }

    public function generateAvatar()
    {
        return 'https://i.pravatar.cc/150?img=' . Arr::random(range(1, 70));
    }

    public function tweet($body, $retweetId = null)
    {
        return $this->tweets()->create([
            'body' => $body,
            'retweeted_id' => $retweetId,
        ]);
    }

    public function retweet(Tweet $oldTweet, $body = null)
    {
        return $oldTweet->retweet($this, $body);
    }
}
