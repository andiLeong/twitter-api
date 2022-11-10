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
        $tweets = $this->getTweets();
        $responseLikeTweet = $this->filterById($tweets, $this->likedTweets->id);
        $responseNotLikeTweet = $this->filterById(
            $tweets,
            $this->notLikedTweets->id,
        );

        $this->assertEquals(0, $responseNotLikeTweet['likes_count']);
        $this->assertEquals(1, $responseLikeTweet['likes_count']);
    }

    /** @test */
    public function it_can_determine_if_logged_in_user_liked_a_tweet()
    {
        $tweets = $this->getTweets();
        $likeTweet = $this->filterById($tweets, $this->likedTweets->id);
        $notLikeTweet = $this->filterById($tweets, $this->notLikedTweets->id);

        $this->assertFalse($notLikeTweet['liked_by_user']);
        $this->assertTrue($likeTweet['liked_by_user']);
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
        $responseTweetIds = $this->getTweets()
            ->pluck('id')
            ->all();

        $this->assertTrue(in_array($this->likedTweets->id, $responseTweetIds));
        $this->assertTrue(in_array($kevinTweet->id, $responseTweetIds));
        $this->assertFalse(in_array($mariaTweet->id, $responseTweetIds));
    }

    /** @test */
    public function it_can_get_retweet_count_of_each_tweet()
    {
        $noRetweetTweet = $this->filterById(
            $this->getTweets(),
            $this->notLikedTweets->id,
        );

        $this->kevin->retweet($this->likedTweets);
        $retweetTweet = $this->filterById(
            $this->getTweets(),
            $this->likedTweets->id,
        );

        $this->assertEquals(0, $noRetweetTweet['retweets_count']);
        $this->assertEquals(1, $retweetTweet['retweets_count']);
    }

    /** @test */
    public function it_can_get_retweet_of_a_tweet()
    {
        $this->kevin->retweet($this->notLikedTweets);
        $tweets = $this->getTweets();

        $retweetTweet = $this->filterById(
            $tweets,
            $this->notLikedTweets->id,
            'retweeted_id',
        )['retweeted_tweet'];

        $this->assertEquals($this->notLikedTweets->body, $retweetTweet['body']);
        $this->assertEquals(
            $this->notLikedTweets->user->avatar,
            $retweetTweet['user']['avatar'],
        );
        $this->assertEquals(
            $this->notLikedTweets->user->name,
            $retweetTweet['user']['name'],
        );
    }

    /** @test */
    public function it_can_check_each_tweet_is_retweeted_by_the_logged_in_user()
    {
        $this->kevin->retweet($this->notLikedTweets);
        $tweets = $this->getTweets();

        $retweetTweet = $this->filterById($tweets, $this->notLikedTweets->id);
        $this->assertTrue($retweetTweet['retweeted_by_user']);
    }

    /** @test */
    public function i_can_take_all_tweets_if_is_all_query_string_is_provided()
    {
        $tweet = Tweet::factory()->create();
        $tweets = $this->getTweets(['is_all' => 1]);

        $this->assertTrue(
            $tweets->contains(fn($tw) => $tw['id'] == $tweet->id),
        );
    }

    public function getTweets($payload = [])
    {
        $query = http_build_query($payload);
        return $this->getJson("/api/tweets?$query")->collect('data');
    }

    public function filterById($tweets, $id, $column = 'id')
    {
        return $tweets->filter(fn($tweet) => $tweet[$column] === $id)->first();
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
