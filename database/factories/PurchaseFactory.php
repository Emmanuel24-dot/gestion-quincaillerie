<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'numero' => 'ACH-'.fake()->unique()->numerify('##########'),
            'date_achat' => now(),
            'statut' => 'receptionnee',
            'sous_total' => 5000,
            'total' => 5000,
        ];
    }
}
