<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ShowTweetTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function it_can_get_single_tweet_information()
    {
        $tweet = Tweet::factory()->create();
        $response = $this->get("/api/tweets/{$tweet->id}");
        $body = $response->json();

        $response->assertStatus(200);
        $this->assertEquals($tweet->body, $body['body']);
        $this->assertEquals($tweet->user->username, $body['user']['username']);
    }

    /** @test */
    public function it_can_get_single_tweet_likes_count()
    {
        $arron = User::factory()->create();
        $tweet = Tweet::factory()->create();
        $tweet->likeBy($arron);

        $response = $this->get("/api/tweets/{$tweet->id}");
        $body = $response->json();
        $this->assertEquals(1, $body['likes_count']);

        $tweet->likeBy($arron);
        $response = $this->get("/api/tweets/{$tweet->id}");
        $body = $response->json();
        $this->assertEquals(0, $body['likes_count']);
    }
}
