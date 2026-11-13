<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_suppliers_can_be_created_updated_and_deleted(): void
    {
        $this->post(route('fournisseurs.store'), [
            'nom' => 'Matériaux du Sénégal',
            'contact_nom' => 'Ibrahima Ba',
            'telephone' => '+221 33 123 45 67',
            'email' => 'ventes@example.test',
            'adresse' => 'Dakar',
            'statut' => 1,
        ])
            ->assertRedirect(route('fournisseurs.index'))
            ->assertSessionHas('success');

        $supplier = Supplier::query()->where('email', 'ventes@example.test')->firstOrFail();
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'nom' => 'Matériaux du Sénégal']);

        $this->put(route('fournisseurs.update', $supplier), [
            'nom' => 'Matériaux Dakar',
            'contact_nom' => 'Ibrahima Ba',
            'telephone' => '+221 33 123 45 67',
            'email' => 'ventes@example.test',
            'adresse' => 'Dakar',
            'statut' => 1,
        ])->assertRedirect(route('fournisseurs.index'));

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'nom' => 'Matériaux Dakar']);

        $this->delete(route('fournisseurs.destroy', $supplier))
            ->assertRedirect(route('fournisseurs.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_supplier_directory_can_be_searched_and_filtered_by_status(): void
    {
        Supplier::factory()->create(['nom' => 'Quincaillerie Dakar']);
        Supplier::factory()->inactive()->create(['nom' => 'Outillage Thiès']);

        $this->get(route('fournisseurs.index', ['search' => 'Dakar', 'statut' => '1']))
            ->assertOk()
            ->assertSee('Quincaillerie Dakar')
            ->assertDontSee('Outillage Thiès');
    }

    public function test_supplier_with_purchases_cannot_be_deleted(): void
    {
        $supplier = Supplier::factory()->create();
        Purchase::factory()->for($supplier)->create();

        $this->delete(route('fournisseurs.destroy', $supplier))
            ->assertRedirect(route('fournisseurs.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($supplier);
    }
}
