<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FollowUserTest extends TestCase
{
    use LazilyRefreshDatabase;

    private mixed $aileen;
    private mixed $green;

    protected function setUp(): void
    {
        parent::setUp();
        $this->green = User::factory()->create();
        $this->aileen = User::factory()->create();
    }

    /** @test */
    public function log_in_user_can_follow_another_user()
    {
        $this->assertFalse($this->green->isFollowing($this->aileen));
        $this->assertDatabaseMissing('follows', [
            'user_id' => $this->green->id,
            'follow_user_id' => $this->aileen->id,
        ]);

        $response = $this->login($this->green)->postJson(
            "/api/follow-toggle/{$this->aileen->id}",
        );
        $response->assertStatus(200);

        $this->assertDatabaseHas('follows', [
            'user_id' => $this->green->id,
            'follow_user_id' => $this->aileen->id,
        ]);
        $this->assertTrue($this->green->fresh()->isFollowing($this->aileen));
    }

    /** @test */
    public function log_in_user_can_unfollow_another_user()
    {
        $this->green->follows($this->aileen);
        $this->assertTrue($this->green->isFollowing($this->aileen));

        $response = $this->login($this->green)->postJson(
            "/api/follow-toggle/{$this->aileen->id}",
        );
        $response->assertStatus(200);

        $this->assertDatabaseMissing('follows', [
            'user_id' => $this->green->id,
            'follow_user_id' => $this->aileen->id,
        ]);
        $this->assertFalse($this->green->fresh()->isFollowing($this->aileen));
    }

    /** @test */
    public function a_user_cant_follow_your_self()
    {
        $response = $this->login($this->green)->postJson(
            "/api/follow-toggle/{$this->green->id}",
        );
        $response->assertForbidden();
    }
}
