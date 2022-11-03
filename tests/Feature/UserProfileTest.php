<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function it_can_get_a_user_profile()
    {
        $user = User::factory()->create();
        $response = $this->get("/api/user/{$user->id}");

        $response->assertStatus(200);
        $this->assertEquals($user->username, $response->json()['username']);
        $this->assertEquals($user->id, $response->json()['id']);
        $this->assertEquals($user->email, $response->json()['email']);
        $this->assertEquals($user->name, $response->json()['name']);
        $this->assertEquals($user->avatar, $response->json()['avatar']);
    }

    /** @test */
    public function it_can_also_get_follower_count_from_profile_endpoint()
    {
        $funny = User::factory()->create();
        $this->assertEquals($funny->follow->count(), 0);

        $follows = User::factory(5)->create();
        $funny->follows($follows);

        $follows->take(2)->each->follows($funny);


        $response = $this->get("/api/user/{$funny->id}");
        $body = $response->json();

        $this->assertArrayHasKey('being_follow_count', $body);
        $this->assertArrayHasKey('follow_count', $body);
        $this->assertEquals(5, $body['follow_count']);
        $this->assertEquals(2, $body['being_follow_count']);
        $this->assertEquals($funny->refresh()->follow->count(), 5);
    }

    /** @test */
    public function it_can_get_false_if_logged_in_user_is_not_following_the_user_being_query()
    {
        $david = User::factory()->create();
        $jimmy = User::factory()->create();
        $this->actingAs($jimmy);

        $body = $this->get("/api/user/{$david->id}")->json();

        $this->assertArrayHasKey('follow_by_logged_in_user', $body);
        $this->assertFalse($body['follow_by_logged_in_user']);
    }

    public function it_can_get_true_if_logged_in_user_is_not_following_the_user_being_query()
    {
        $david = User::factory()->create();
        $jimmy = User::factory()->create();

        $jimmy->follows($david);
        $body2 = $this->get("/api/user/{$david->id}")->json();
        $this->assertTrue($body2['follow_by_logged_in_user']);
    }
}
