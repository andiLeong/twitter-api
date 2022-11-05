<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LikeTweetTest extends TestCase
{
    use LazilyRefreshDatabase;

    private mixed $tweet;
    private mixed $jane;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jane = User::factory()->create();
        $this->tweet = Tweet::factory()->create();
    }

    /** @test */
    public function it_can_like_a_tweet()
    {
        $response = $this->login($this->jane)->postJson(
            "api/like-tweet-toggle/{$this->tweet->id}",
        );

        $response->assertStatus(200);
        $this->assertTrue($this->tweet->likedBy($this->jane));
        $this->assertDatabaseHas('tweet_likes', [
            'user_id' => $this->jane->id,
            'tweet_id' => $this->tweet->id,
        ]);
    }

    /** @test */
    public function it_can_unlike_a_tweet()
    {
        $this->jane = User::factory()->create();
        $this->tweet = Tweet::factory()->create();
        $this->tweet->likeBy($this->jane);
        $this->assertDatabaseHas('tweet_likes', [
            'user_id' => $this->jane->id,
            'tweet_id' => $this->tweet->id,
        ]);

        $this->login($this->jane)->postJson(
            "api/like-tweet-toggle/{$this->tweet->id}",
        );

        $this->assertFalse($this->tweet->likedBy($this->jane));
        $this->assertDatabaseMissing('tweet_likes', [
            'user_id' => $this->jane->id,
            'tweet_id' => $this->tweet->id,
        ]);
    }
}
