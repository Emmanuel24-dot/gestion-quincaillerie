<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseItem>
 */
class PurchaseItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_id' => Purchase::factory(),
            'product_id' => Product::factory(),
            'reference_produit' => fake()->bothify('PRD-####'),
            'nom_produit' => fake()->words(2, true),
            'unite_symbole' => 'pce',
            'quantite' => 1,
            'prix_unitaire' => 1000,
            'total_ligne' => 1000,
        ];
    }
}
