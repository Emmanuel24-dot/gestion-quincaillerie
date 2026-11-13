<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_can_be_created_updated_and_deleted(): void
    {
        $this->post(route('clients.store'), [
            'nom' => 'Awa Diop',
            'telephone' => '+221 77 123 45 67',
            'email' => 'awa@example.test',
            'adresse' => 'Dakar',
            'statut' => 1,
        ])
            ->assertRedirect(route('clients.index'))
            ->assertSessionHas('success');

        $customer = Customer::query()->where('email', 'awa@example.test')->firstOrFail();
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'nom' => 'Awa Diop', 'statut' => 1]);

        $this->put(route('clients.update', $customer), [
            'nom' => 'Awa Ndiaye',
            'telephone' => '+221 77 123 45 67',
            'email' => 'awa@example.test',
            'adresse' => 'Thiès',
            'statut' => 1,
        ])->assertRedirect(route('clients.index'));

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'nom' => 'Awa Ndiaye', 'adresse' => 'Thiès']);

        $this->delete(route('clients.destroy', $customer))
            ->assertRedirect(route('clients.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_customer_directory_can_be_searched_and_filtered_by_status(): void
    {
        Customer::factory()->create(['nom' => 'Awa Diop', 'telephone' => '770001111']);
        Customer::factory()->inactive()->create(['nom' => 'Mamadou Fall', 'telephone' => '770002222']);

        $this->get(route('clients.index', ['search' => 'Awa', 'statut' => '1']))
            ->assertOk()
            ->assertSee('Awa Diop')
            ->assertDontSee('Mamadou Fall');
    }

    public function test_customer_with_sales_cannot_be_deleted(): void
    {
        $customer = Customer::factory()->create(['nom' => 'Awa Diop']);
        Sale::factory()->create(['customer_id' => $customer->id, 'client_nom' => $customer->nom]);

        $this->delete(route('clients.destroy', $customer))
            ->assertRedirect(route('clients.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($customer);
    }

    public function test_customer_balance_ignores_refunded_returns(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['prix_vente' => 100, 'quantite_stock' => 10]);

        $this->post(route('ventes.store'), [
            'customer_id' => $customer->id,
            'moyen_paiement' => 'especes',
            'montant_recu' => 500,
            'items' => [['product_id' => $product->id, 'quantite' => 10]],
        ])->assertSessionHasNoErrors();
        $sale = Sale::query()->with('items')->firstOrFail();

        $this->post(route('ventes.retours.store', $sale), [
            'motif' => 'Article abîmé',
            'moyen_remboursement' => 'especes',
            'items' => [['sale_item_id' => $sale->items->first()->id, 'quantite' => 2]],
        ])->assertSessionHasNoErrors();

        $this->get(route('clients.show', $customer))
            ->assertOk()
            ->assertViewHas('balance', 500.0)
            ->assertViewHas('totalReturned', 200.0);
    }
}
