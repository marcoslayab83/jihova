<?php

namespace Database\Factories;

use App\Models\MallAdBanner;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MallAdBanner>
 */
class MallAdBannerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'product_id' => Product::factory(),
            'headline' => ucfirst(fake()->words(4, true)),
            'subline' => fake()->sentence(8),
            'badge' => '✦ Promoción Especial',
            'tag' => 'Desde $'.fake()->numberBetween(10, 80),
            'image_url' => '/images/catalog/hero-mall.jpg',
            'target_url' => null,
            'placement' => 'hero_spotlight',
            'is_active' => true,
            'clicks_count' => fake()->numberBetween(0, 100),
            'impressions_count' => fake()->numberBetween(50, 500),
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(14),
        ];
    }
}
