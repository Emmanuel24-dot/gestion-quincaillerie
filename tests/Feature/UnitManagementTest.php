<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_units_can_be_searched_and_filtered_by_status(): void
    {
        Unit::factory()->create(['nom' => 'Kilogramme', 'symbole' => 'kg']);
        Unit::factory()->create(['nom' => 'Litre', 'symbole' => 'L', 'statut' => false]);

        $this->get(route('unites.index', ['search' => 'kg', 'statut' => '1']))
            ->assertOk()
            ->assertSee('Kilogramme')
            ->assertDontSee('Litre');
    }

    public function test_units_can_be_created_updated_and_deleted(): void
    {
        $this->post(route('unites.store'), [
            'nom' => 'Kilogramme',
            'symbole' => 'kg',
            'statut' => 1,
        ])
            ->assertRedirect(route('unites.index'))
            ->assertSessionHas('success');

        $unit = Unit::query()->where('symbole', 'kg')->firstOrFail();
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'nom' => 'Kilogramme']);

        $this->put(route('unites.update', $unit), [
            'nom' => 'Kilogramme',
            'symbole' => 'kgs',
            'statut' => 1,
        ])
            ->assertRedirect(route('unites.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('units', ['id' => $unit->id, 'symbole' => 'kgs']);

        $this->delete(route('unites.destroy', $unit))
            ->assertRedirect(route('unites.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('units', ['id' => $unit->id]);
    }

    public function test_duplicate_unit_symbols_are_rejected(): void
    {
        Unit::factory()->create(['symbole' => 'kg']);

        $this->post(route('unites.store'), [
            'nom' => 'Kilogramme bis',
            'symbole' => 'kg',
        ])->assertSessionHasErrors('symbole');

        $this->assertDatabaseCount('units', 1);
    }

    public function test_unit_used_by_products_cannot_be_deleted(): void
    {
        $product = Product::factory()->create();
        $unit = $product->unit;

        $this->delete(route('unites.destroy', $unit))
            ->assertRedirect(route('unites.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($unit);
    }

    public function test_unit_forms_and_sidebar_link_are_available(): void
    {
        $unit = Unit::factory()->create(['nom' => 'Mètre', 'symbole' => 'm']);

        $this->get(route('unites.create'))
            ->assertOk()
            ->assertSee('Nouvelle unité');

        $this->get(route('unites.edit', $unit))
            ->assertOk()
            ->assertSee('Mètre');

        $this->get(route('unites.index'))
            ->assertOk()
            ->assertSee(route('unites.index'))
            ->assertSee('aria-current="page"', false);
    }
}
