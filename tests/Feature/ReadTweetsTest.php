<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReadTweetsTest extends TestCase
{
    use LazilyRefreshDatabase;

    private mixed $kevin;
    private mixed $likedTweets;
    private mixed $notLikedTweets;
    private mixed $jennifer;

    public function setUp(): void
    {
        parent::setUp();
        $this->kevin = User::factory()->create();
        $this->jennifer = User::factory()->create();
        $this->loginAsKevin()
            ->kevinFollowsJennifer()
            ->JenniferCreate2Tweets()
            ->oneTweetIsLikedByKevin();
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

    /** @test */
    public function it_only_return_list_of_tweets_logged_in_user_follows_and_logged_in_users_own_tweet()
    {
        $maria = User::factory()->create();
        $kevinTweet = Tweet::factory()->create([
            'user_id' => $this->kevin->id,
        ]);

        $mariaTweet = Tweet::factory()->create([
            'user_id' => $maria->id,
        ]);

        $this->assertFalse($this->kevin->isFollowing($maria));
        $responseTweetIds = $this->get('/api/tweets')
            ->collect('data')
            ->pluck('id')
            ->all();

        $this->assertTrue(in_array($this->likedTweets->id, $responseTweetIds));
        $this->assertTrue(in_array($kevinTweet->id, $responseTweetIds));
        $this->assertFalse(in_array($mariaTweet->id, $responseTweetIds));
    }

    private function loginAsKevin()
    {
        $this->login($this->kevin);
        return $this;
    }

    private function kevinFollowsJennifer()
    {
        $this->kevin->follows($this->jennifer);
        return $this;
    }

    private function JenniferCreate2Tweets()
    {
        $user = [
            'user_id' => $this->jennifer->id,
        ];

        $this->likedTweets = Tweet::factory()->create($user);
        $this->notLikedTweets = Tweet::factory()->create($user);
        return $this;
    }

    private function oneTweetIsLikedByKevin()
    {
        $this->likedTweets->likeBy($this->kevin);
        return $this;
    }
}
