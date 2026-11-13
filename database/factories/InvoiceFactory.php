<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
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
            'numero' => 'FAC-'.fake()->unique()->numerify('########-######'),
            'date_emission' => now(),
            'statut' => 'emise',
        ];
    }
}
