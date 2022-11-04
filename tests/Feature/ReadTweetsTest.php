<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReadTweetsTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function it_gets_a_tweet_count_when_fetch_tweet_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $likedTweets = Tweet::factory()->create();
        $notLikedTweets = Tweet::factory()->create();
        $likedTweets->likeBy($user);

        $response = $this->get('/api/tweets')->collect('data');
        $responseLikeTweet = $response->filter(fn($tweet) => $tweet['id'] === $likedTweets->id)->first();
        $responseNotLikeTweet = $response->filter(fn($tweet) => $tweet['id'] === $notLikedTweets->id)->first();

        $this->assertEquals(0, $responseNotLikeTweet['likes_count']);
        $this->assertEquals(1, $responseLikeTweet['likes_count']);

    }
}
