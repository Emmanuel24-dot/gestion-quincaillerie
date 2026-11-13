<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItem>
 */
class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'reference_produit' => 'PRD-TEST',
            'nom_produit' => 'Produit de test',
            'unite_symbole' => 'pce',
            'quantite' => 1,
            'prix_unitaire' => 5000,
            'total_ligne' => 5000,
        ];
    }
}
