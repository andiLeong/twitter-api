<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LikeTweetTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function it_can_like_a_tweet()
    {
        $jane = User::factory()->create();
        $this->actingAs($jane);
        $tweet = Tweet::factory()->create();
        $response = $this->postJson("api/like-tweet-toggle/{$tweet->id}");

        $response->assertStatus(200);
        $this->assertTrue($tweet->likedBy($jane));
        $this->assertDatabaseHas('tweet_likes', [
            'user_id' => $jane->id,
            'tweet_id' => $tweet->id,
        ]);
    }

    /** @test */
    public function it_can_unlike_a_tweet()
    {
        $jane = User::factory()->create();
        $this->actingAs($jane);
        $tweet = Tweet::factory()->create();
        $tweet->likeBy($jane);
        $this->assertDatabaseHas('tweet_likes', [
            'user_id' => $jane->id,
            'tweet_id' => $tweet->id,
        ]);

        $this->postJson("api/like-tweet-toggle/{$tweet->id}");

        $this->assertFalse($tweet->likedBy($jane));
        $this->assertDatabaseMissing('tweet_likes', [
            'user_id' => $jane->id,
            'tweet_id' => $tweet->id,
        ]);
    }
}
