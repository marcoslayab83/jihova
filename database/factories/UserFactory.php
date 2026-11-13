<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('+58 412 ### ####'),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Customer,
            'remember_token' => Str::random(10),
        ];
    }

    public function customer(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Customer,
        ]);
    }

    public function merchantAdmin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::MerchantAdmin,
        ]);
    }

    public function driver(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Driver,
        ]);
    }

    public function platformAdmin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::PlatformAdmin,
        ]);
    }
}
