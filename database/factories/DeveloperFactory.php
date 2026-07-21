<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeveloperFactory extends Factory
{
    public function definition(): array
    {
        $login = fake()->unique()->userName();

        return [
            'github_id' => fake()->unique()->numberBetween(1, 10000000),
            'login' => $login,
            'name' => fake()->name(),
            'avatar_url' => 'https://avatars.githubusercontent.com/u/1',
            'html_url' => 'https://github.com/'.$login,
            'location' => fake()->city(),
            'primary_language' => 'PHP',
            'languages' => ['PHP', 'TypeScript'],
            'followers' => fake()->numberBetween(0, 1000),
            'public_repositories' => fake()->numberBetween(1, 100),
            'total_stars' => fake()->numberBetween(0, 5000),
            'last_synced_at' => now(),
            'sync_status' => 'synced',
        ];
    }
}
