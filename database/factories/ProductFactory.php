<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => fake()->unique()->numerify('PRD-#####'),
            'nom' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'prix_achat' => fake()->randomFloat(2, 100, 100000),
            'prix_vente' => fake()->randomFloat(2, 100, 150000),
            'quantite_stock' => fake()->randomFloat(3, 0, 100),
            'seuil_alerte' => fake()->randomFloat(3, 0, 10),
            'statut' => true,
        ];
    }
}
