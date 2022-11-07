<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RetweetTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function it_can_retweet_a_tweet()
    {
        $user = User::factory()->create();
        $tweet = Tweet::factory()->create();
        $this->assertNull($tweet->retweeted_id);
        $this->assertDatabaseMissing('retweets', [
            'user_id' => $user->id,
            'tweet_id' => $tweet->id,
        ]);

        $response = $this->login($user)->postJson("/api/retweet/{$tweet->id}", [
            'body' => 'retweet',
        ]);
        $newTweet = Tweet::whereBody('retweet')->first();

        $this->assertNotNull($newTweet);
        $this->assertEquals($tweet->id, $newTweet->fresh()->retweeted_id);
        $response->assertStatus(201);
        $this->assertDatabaseHas('retweets', [
            'user_id' => $user->id,
            'tweet_id' => $tweet->id,
        ]);
    }

    /** @test */
    public function it_can_retweet_a_tweet_event_body_is_null()
    {
        $user = User::factory()->create();
        $tweet = Tweet::factory()->create();
        $this->assertNull($tweet->retweeted_id);
        $this->assertDatabaseMissing('retweets', [
            'user_id' => $user->id,
            'tweet_id' => $tweet->id,
        ]);

        $response = $this->login($user)->postJson("/api/retweet/{$tweet->id}");
        $newTweet = Tweet::whereId($tweet->id + 1)->first();

        $this->assertNotNull($newTweet);
        $this->assertEquals($tweet->id, $newTweet->fresh()->retweeted_id);
        $response->assertStatus(201);
        $this->assertDatabaseHas('retweets', [
            'user_id' => $user->id,
            'tweet_id' => $tweet->id,
        ]);
    }
}
