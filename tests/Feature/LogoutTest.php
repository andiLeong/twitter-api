<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function it_can_destroy_logged_in_user_access_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('ios')->plainTextToken;
        $this->assertnotempty($user->tokens);

        $this->postJson('api/logout', [], [
            'Authorization' => "Bearer {$token}"
        ]);

        $this->assertempty($user->refresh()->tokens);
    }

}
