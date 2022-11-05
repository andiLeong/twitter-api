<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DeleteTweetTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function a_tweet_can_be_deleted()
    {
        $user = User::factory()->create();
        $this->login($user);
        $tweet = Tweet::factory()->create([
            'user_id' => $user->id
        ]);
        $response = $this->deleteJson("/api/tweets/{$tweet->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tweets', [
            'id' => $tweet->id
        ]);
    }

    /** @test */
    public function a_tweet_can_not_be_deleted_if_not_user_is_not_the_owner()
    {
        $this->login();
        $tweet = Tweet::factory()->create();
        $response = $this->deleteJson("/api/tweets/{$tweet->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('tweets', [
            'id' => $tweet->id
        ]);
    }
}
