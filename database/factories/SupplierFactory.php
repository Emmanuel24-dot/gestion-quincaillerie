<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->company(),
            'contact_nom' => fake()->name(),
            'telephone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'adresse' => fake()->optional()->address(),
            'statut' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['statut' => false]);
    }
}
