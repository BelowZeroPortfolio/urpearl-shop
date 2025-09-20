<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
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
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'role' => \App\Enums\UserRole::BUYER,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an admin user.
     *
     * @return $this
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => \App\Enums\UserRole::ADMIN,
        ]);
    }

    /**
     * Create a buyer user (default role).
     *
     * @return $this
     */
    public function buyer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => \App\Enums\UserRole::BUYER,
        ]);
    }

    /**
     * Create a user with a specific email.
     *
     * @param string $email
     * @return $this
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }
}
