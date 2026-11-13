<?php

namespace Database\Factories;

use App\Enums\StoreStatus;
use App\Models\Category;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'owner_id' => User::factory()->merchantAdmin(),
            'category_id' => Category::factory(),
            'business_name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 999),
            'tagline' => fake()->catchPhrase(),
            'description' => fake()->paragraph(2),
            'logo_url' => null,
            'banner_url' => null,
            'accent_color' => '#4f46e5',
            'phone' => fake()->numerify('+58 212 ### ####'),
            'status' => StoreStatus::Active,
            'commission_rate' => 10.00,
            'rating' => 4.8,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => StoreStatus::Pending,
        ]);
    }
}
