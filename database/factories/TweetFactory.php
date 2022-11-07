<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tweet>
 */
class TweetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $day = Arr::random(range(1, 10));
        return [
            'user_id' => User::factory(),
            'body' => $this->faker->sentence(10),
            'created_at' => now()->subDays($day),
            'retweeted_id' => null,
        ];
    }
}
