<?php

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero' => 'VTE-'.fake()->unique()->numerify('##########'),
            'date_vente' => now(),
            'client_nom' => fake()->optional()->name(),
            'moyen_paiement' => 'especes',
            'statut' => 'payee',
            'sous_total' => 5000,
            'total' => 5000,
            'montant_recu' => 5000,
            'montant_paye' => 5000,
            'monnaie_rendue' => 0,
        ];
    }
}
