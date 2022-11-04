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
    public function it_gets_a_tweet_count()
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

    /** @test */
    public function it_can_determine_if_logged_in_user_liked_a_tweet()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $likedTweets = Tweet::factory()->create();
        $notLikedTweets = Tweet::factory()->create();
        $likedTweets->likeBy($user);

        $response = $this->get('/api/tweets')->collect('data');
        $responseLikeTweet = $response->filter(fn($tweet) => $tweet['id'] === $likedTweets->id)->first();
        $responseNotLikeTweet = $response->filter(fn($tweet) => $tweet['id'] === $notLikedTweets->id)->first();

        $this->assertFalse($responseNotLikeTweet['liked_by_user']);
        $this->assertTrue($responseLikeTweet['liked_by_user']);
    }
}
