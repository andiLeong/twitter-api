<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;
use Tests\ValidationTester;

class LoginTest extends TestCase
{
    use LazilyRefreshDatabase;

    private ValidationTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

//        $user = User::factory()->create();
        $this->tester = new ValidationTester('post', '/api/login', [
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ], $this);
    }

    /** @test */
    public function test_validation()
    {
        $this->tester->run();
    }

    /** @test */
    public function it_can_create_user_access_token()
    {
        $this->assertdatabasecount('personal_access_tokens', 0);
        $user = User::factory()->create();
        $response = $this->tester->fire([
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'ios',
        ]);

        $this->assertnotempty($user->tokens);
        $this->assertnotempty($response->json()['token']);
        $this->assertEquals($user['email'], $response->json()['user']['email']);
    }
}
