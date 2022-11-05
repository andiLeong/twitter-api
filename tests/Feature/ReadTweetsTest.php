<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReadTweetsTest extends TestCase
{
    use LazilyRefreshDatabase;

    private mixed $user;
    private mixed $likedTweets;
    private mixed $notLikedTweets;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->login($this->user);

        $this->likedTweets = Tweet::factory()->create();
        $this->notLikedTweets = Tweet::factory()->create();
        $this->likedTweets->likeBy($this->user);
    }

    /** @test */
    public function it_gets_a_tweet_likes_count()
    {
        $response = $this->get('/api/tweets')->collect('data');
        $responseLikeTweet = $response
            ->filter(fn($tweet) => $tweet['id'] === $this->likedTweets->id)
            ->first();
        $responseNotLikeTweet = $response
            ->filter(fn($tweet) => $tweet['id'] === $this->notLikedTweets->id)
            ->first();

        $this->assertEquals(0, $responseNotLikeTweet['likes_count']);
        $this->assertEquals(1, $responseLikeTweet['likes_count']);
    }

    /** @test */
    public function it_can_determine_if_logged_in_user_liked_a_tweet()
    {
        $response = $this->get('/api/tweets')->collect('data');
        $responseLikeTweet = $response
            ->filter(fn($tweet) => $tweet['id'] === $this->likedTweets->id)
            ->first();
        $responseNotLikeTweet = $response
            ->filter(fn($tweet) => $tweet['id'] === $this->notLikedTweets->id)
            ->first();

        $this->assertFalse($responseNotLikeTweet['liked_by_user']);
        $this->assertTrue($responseLikeTweet['liked_by_user']);
    }
}
