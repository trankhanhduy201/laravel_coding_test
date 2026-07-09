<?php

namespace Database\Factories;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;
    public const DEMO_PASSWORD = '@Aa12345';

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make(self::DEMO_PASSWORD),
            'remember_token' => Str::random(10),
            'role' => UserRoleEnum::User,
        ];
    }

    /**
     * Create a factory state for a superuser.
     */
    public function superuser(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Superuser',
            'email' => 'superuser@example.com',
            'role' => UserRoleEnum::Admin,
        ]);
    }

    /**
     * Create a factory state for a normal user.
     */
    public function normalUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'role' => UserRoleEnum::User,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
