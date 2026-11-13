<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->state(['quantite_stock' => 4]),
            'type' => 'stock_initial',
            'quantite' => 4,
            'stock_avant' => 0,
            'stock_apres' => 4,
            'motif' => 'Stock initial',
            'notes' => null,
        ];
    }
}
