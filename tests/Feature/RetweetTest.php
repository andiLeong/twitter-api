<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RetweetTest extends TestCase
{
    use LazilyRefreshDatabase;

    private mixed $candy;
    private mixed $tweet;

    public function setUp(): void
    {
        parent::setUp();
        $this->candy = User::factory()->create();
        $this->tweet = Tweet::factory()->create();
    }

    /** @test */
    public function it_can_retweet_a_tweet()
    {
        $this->assertNull($this->tweet->retweeted_id);
        $this->assertDatabaseMissing('retweets', [
            'user_id' => $this->candy->id,
            'tweet_id' => $this->tweet->id,
        ]);

        $response = $this->retweet(null, [
            'body' => 'retweet',
        ]);

        $newTweet = Tweet::whereBody('retweet')->first();

        $this->assertNotNull($newTweet);
        $this->assertEquals($this->tweet->id, $newTweet->fresh()->retweeted_id);
        $response->assertStatus(201);
        $this->assertDatabaseHas('retweets', [
            'user_id' => $this->candy->id,
            'tweet_id' => $this->tweet->id,
        ]);
    }

    /** @test */
    public function it_can_retweet_a_tweet_event_body_is_null()
    {
        $response = $this->retweet();
        $newTweet = Tweet::whereId($this->tweet->id + 1)->first();

        $this->assertNotNull($newTweet);
        $this->assertEquals($this->tweet->id, $newTweet->fresh()->retweeted_id);
        $response->assertStatus(201);
        $this->assertDatabaseHas('retweets', [
            'user_id' => $this->candy->id,
            'tweet_id' => $this->tweet->id,
        ]);
    }

    /** @test */
    public function a_retweeted_tweet_cant_be_retweet_again()
    {
        $retweetedTweet = Tweet::factory()->create([
            'retweeted_id' => $this->tweet->id,
        ]);

        $this->assertTrue($retweetedTweet->isRetweet());

        $response = $this->retweet($retweetedTweet->id);
        $response->assertStatus(403);
    }

    /** @test */
    public function a_retweet_can_be_undo()
    {
        $this->retweet();
        $this->assertDatabaseHas('retweets', [
            'user_id' => $this->candy->id,
            'tweet_id' => $this->tweet->id,
        ]);
        $newRetweetTweet = Tweet::where('user_id', $this->candy->id)
            ->where('retweeted_id', $this->tweet->id)
            ->first();
        $this->assertNotNull($newRetweetTweet);

        $response = $this->retweet();
        $this->assertDatabaseMissing('retweets', [
            'user_id' => $this->candy->id,
            'tweet_id' => $this->tweet->id,
        ]);
        $this->assertDatabaseMissing('tweets', [
            'user_id' => $this->candy->id,
            'retweeted_id' => $newRetweetTweet->id,
        ]);
    }

    public function retweet($tweetId = null, $payload = [])
    {
        $tweetId ??= $this->tweet->id;
        return $this->login($this->candy)->postJson(
            "/api/retweet/{$tweetId}",
            $payload,
        );
    }
}
