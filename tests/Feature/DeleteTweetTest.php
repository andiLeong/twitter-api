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
        $tweet = Tweet::factory()->create([
            'user_id' => $user->id,
        ]);
        $response = $this->login($user)->deleteJson("/api/tweets/{$tweet->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tweets', [
            'id' => $tweet->id,
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
            'id' => $tweet->id,
        ]);
    }

    /** @test */
    public function when_a_tweet_is_deleted_all_retweet_record_should_be_deleted()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $this->login($user);
        Tweet::factory()->create();
        $tweet = Tweet::factory()->create();
        $user->retweet($tweet);

        $this->assertDatabaseHas('retweets', [
            'user_id' => $user->id,
            'tweet_id' => $tweet->id,
        ]);

        $this->deleteTweetFromRetweet($tweet, $user);

        $this->assertEmpty($user->retweets);
        $this->assertDatabaseMissing('retweets', [
            'user_id' => $user->id,
            'tweet_id' => $tweet->id,
        ]);
    }

    private function deleteTweetFromRetweet(mixed $tweet, $user)
    {
        $newTweet = Tweet::where('retweeted_id', $tweet->id)
            ->where('user_id', $user->id)
            ->first();
        return $this->deleteJson("/api/tweets/{$newTweet->id}");
    }
}
