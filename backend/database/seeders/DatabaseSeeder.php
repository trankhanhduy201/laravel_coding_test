<?php

namespace Database\Seeders;

use App\Models\Task;
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
            ['email' => 'manager@example.com'],
            array_merge(
                User::factory()->normalUser()->make()->getAttributes(),
                [
                    'name' => 'Manager',
                    'email' => 'manager@example.com',
                    'email_verified_at' => now(),
                    'password' => UserFactory::DEMO_PASSWORD,
                ]
            )
        );

        User::query()->updateOrCreate(
            ['email' => 'developer@example.com'],
            array_merge(
                User::factory()->normalUser()->make()->getAttributes(),
                [   
                    'name' => 'Developer',
                    'email' => 'developer@example.com',
                    'email_verified_at' => now(),
                    'password' => UserFactory::DEMO_PASSWORD,
                ]
            )
        );

        User::query()->updateOrCreate(
            ['email' => 'tester@example.com'],
            array_merge(
                User::factory()->normalUser()->make()->getAttributes(),
                [
                    'name' => 'Tester',
                    'email' => 'tester@example.com',
                    'email_verified_at' => now(),
                    'password' => UserFactory::DEMO_PASSWORD,
                ]
            )
        );

        Task::query()->forceDelete();

        Task::factory(30)->create();
    }
}
