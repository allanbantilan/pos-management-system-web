<?php

namespace Database\Factories;

use App\Models\PosItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PosItem>
 */
class PosItemFactory extends Factory
{
    protected $model = PosItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'sku' => 'SKU-'.strtoupper(Str::random(8)),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 10, 500),
            'cost' => fake()->randomFloat(2, 5, 100),
            'category' => 'general',
            'stock' => 100,
            'min_stock' => 5,
            'is_active' => true,
            'unit' => 'pcs',
            'metadata' => [],
        ];
    }
}
