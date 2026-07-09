<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Database\Factories\UserFactory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'superuser@example.com'],
            array_merge(
                User::factory()->superuser()->make()->getAttributes(),
                [
                    'email_verified_at' => now(),
                    'password' => UserFactory::DEMO_PASSWORD,
                ]
            )
        );

        User::query()->updateOrCreate(
            ['email' => 'user@example.com'],
            array_merge(
                User::factory()->normalUser()->make()->getAttributes(),
                [
                    'email_verified_at' => now(),
                    'password' => UserFactory::DEMO_PASSWORD,
                ]
            )
        );
    }
}
