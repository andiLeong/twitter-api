<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FollowUserTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function log_in_user_can_follow_another_user()
    {
        $green = User::factory()->create();
        $this->actingAs($green);
        $aileen = User::factory()->create();
        $this->assertFalse($green->isFollowing($aileen));
        $this->assertDatabaseMissing('follows', [
            'user_id' => $green->id,
            'follow_user_id' => $aileen->id,
        ]);

        $response = $this->postJson("/api/follow-toggle/{$aileen->id}");
        $response->assertStatus(200);

        $this->assertDatabaseHas('follows', [
            'user_id' => $green->id,
            'follow_user_id' => $aileen->id,
        ]);
        $this->assertTrue($green->fresh()->isFollowing($aileen));
    }

    /** @test */
    public function log_in_user_can_unfollow_another_user()
    {
        $green = User::factory()->create();
        $this->actingAs($green);
        $aileen = User::factory()->create();
        $green->follows($aileen);
        $this->assertTrue($green->isFollowing($aileen));

        $response = $this->postJson("/api/follow-toggle/{$aileen->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('follows', [
            'user_id' => $green->id,
            'follow_user_id' => $aileen->id,
        ]);
        $this->assertFalse($green->fresh()->isFollowing($aileen));
    }
}
