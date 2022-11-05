<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name' => 'andi',
            'email' => 'andi@andi.com',
        ]);

        $users = User::factory(49)->create();

        User::all()->each(function ($user) {
            Tweet::factory()->create([
                'user_id' => $user->id,
            ]);
        });

        $this->setUpUserFollows();

        Tweet::latest('id')
            ->take(10)
            ->get()
            ->each(function ($tweet) {
                User::factory(20)
                    ->create()
                    ->each(fn($user) => $tweet->likeBy($user));
            });
    }

    private function setUpUserFollows()
    {
        (new FollowUserSeed())->run();
    }
}
