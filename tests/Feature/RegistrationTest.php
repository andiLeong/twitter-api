<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;
use Tests\ValidationTester;

class RegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    private ValidationTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->tester = new ValidationTester('post', '/api/register', [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|min:3|max:20',
            'username' => 'required|min:3|max:20|unique:users,username',
            'password' => 'required|confirmed',
            'device_name' => 'required',
        ], $this, [
            'username' => [
                'unique' => $user
            ],
            'email' => [
                'unique' => $user
            ]
        ]);
    }

    /** @test */
    public function test_validation()
    {
        $this->tester->run();
    }

    /** @test */
    public function it_can_register_a_user()
    {
        $payload = $this->correctFields();
        $this->assertDatabaseMissing('users', [
            'email' => $payload['email']
        ]);

        $response = $this->tester->fire($payload);

        $user = User::whereEmail($payload['email'])->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->avatar);
        $this->assertEquals($payload['username'],$user->username);
        $this->assertEquals($payload['name'],$user->name);

        $response->assertStatus(200);
        $this->assertEquals($payload['email'], $response->json()['user']['email']);
    }

    /** @test */
    public function after_register_user_token_is_created_and_sent_token_back_to_front_end()
    {
        $this->assertdatabasecount('personal_access_tokens', 0);
        $payload = $this->correctfields();
        $response = $this->tester->fire($payload);

        $user = user::where('email', $payload['email'])->first();
        $this->assertnotempty($user->tokens);
        $this->assertnotempty($response->json()['token']);
    }

    public function correctFields($overrides = [])
    {
        return array_merge([
            'email' => 'exmaple@example.com',
            'username' => 'dummy',
            'name' => 'cindy',
            'password' => 'password',
            'password_confirmation' => 'password',
            'device_name' => 'ios',
        ], $overrides);

    }
}
