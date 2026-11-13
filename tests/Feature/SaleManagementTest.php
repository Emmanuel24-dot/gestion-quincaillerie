<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_sale_items_and_stock_movements_atomically(): void
    {
        $firstProduct = Product::factory()->create([
            'reference' => 'PE-101',
            'nom' => 'Peinture blanche',
            'prix_vente' => 1250.50,
            'quantite_stock' => 10,
        ]);
        $secondProduct = Product::factory()->create([
            'reference' => 'BR-201',
            'nom' => 'Brosse large',
            'prix_vente' => 2100,
            'quantite_stock' => 5,
        ]);

        $this->post(route('ventes.store'), [
            'client_nom' => 'Awa Diop',
            'moyen_paiement' => 'especes',
            'montant_recu' => 6000,
            'items' => [
                ['product_id' => $firstProduct->id, 'quantite' => 2],
                ['product_id' => $secondProduct->id, 'quantite' => 1.5],
            ],
        ])
            ->assertSessionHas('success')
            ->assertRedirect();

        $sale = Sale::query()->with('items')->firstOrFail();

        $this->assertSame('5651.00', $sale->total);
        $this->assertSame('349.00', $sale->monnaie_rendue);
        $this->assertSame('Awa Diop', $sale->client_nom);
        $this->assertCount(2, $sale->items);
        $this->assertSame('8.000', $firstProduct->fresh()->quantite_stock);
        $this->assertSame('3.500', $secondProduct->fresh()->quantite_stock);
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $firstProduct->id,
            'reference_produit' => 'PE-101',
            'prix_unitaire' => '1250.50',
            'total_ligne' => '2501.00',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $secondProduct->id,
            'type' => 'sortie',
            'quantite' => '1.500',
            'stock_avant' => '5.000',
            'stock_apres' => '3.500',
            'motif' => "Vente {$sale->numero}",
        ]);
    }

    public function test_checkout_links_an_existing_customer_and_keeps_the_sale_name_snapshot(): void
    {
        $customer = Customer::factory()->create(['nom' => 'Awa Diop']);
        $product = Product::factory()->create([
            'prix_vente' => 2500,
            'quantite_stock' => 3,
        ]);
        $data = $this->saleData($product, 1, 2500);
        $data['customer_id'] = $customer->id;

        $this->post(route('ventes.store'), $data)->assertRedirect();

        $sale = Sale::query()->firstOrFail();
        $this->assertSame($customer->id, $sale->customer_id);
        $this->assertSame('Awa Diop', $sale->client_nom);
    }

    public function test_checkout_rolls_back_when_stock_is_insufficient(): void
    {
        $product = Product::factory()->create(['quantite_stock' => 1]);

        $this->from(route('ventes.create'))
            ->post(route('ventes.store'), $this->saleData($product, 2, 5000))
            ->assertSessionHasErrors('items.0.quantite');

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertSame('1.000', $product->fresh()->quantite_stock);
        $this->assertDatabaseMissing('stock_movements', [
            'product_id' => $product->id,
            'type' => 'sortie',
        ]);
    }

    public function test_checkout_rejects_partial_payment_for_a_walk_in_customer(): void
    {
        $product = Product::factory()->create([
            'prix_vente' => 2500,
            'quantite_stock' => 4,
        ]);

        $this->from(route('ventes.create'))
            ->post(route('ventes.store'), $this->saleData($product, 2, 4000))
            ->assertSessionHasErrors('customer_id');

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame('4.000', $product->fresh()->quantite_stock);
    }

    public function test_client_cannot_override_the_product_price(): void
    {
        $product = Product::factory()->create([
            'prix_vente' => 3000,
            'quantite_stock' => 4,
        ]);
        $data = $this->saleData($product, 1, 3000);
        $data['items'][0]['prix_unitaire'] = 1;

        $this->from(route('ventes.create'))
            ->post(route('ventes.store'), $data)
            ->assertSessionHasErrors('items.0');

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame('4.000', $product->fresh()->quantite_stock);
    }

    public function test_sales_history_and_printable_receipt_are_available(): void
    {
        $sale = Sale::factory()->create(['numero' => 'VTE-20261004-000123']);
        $sale->items()->create([
            'product_id' => Product::factory()->create()->id,
            'reference_produit' => 'PRD-123',
            'nom_produit' => 'Perceuse',
            'unite_symbole' => 'pce',
            'quantite' => 1,
            'prix_unitaire' => 5000,
            'total_ligne' => 5000,
        ]);

        $this->get(route('ventes.index'))
            ->assertOk()
            ->assertSee('VTE-20261004-000123');

        $this->get(route('ventes.show', $sale))
            ->assertOk()
            ->assertSee('VTE-20261004-000123')
            ->assertSee('Perceuse')
            ->assertSee('Espèces')
            ->assertSee('Imprimer');
    }

    public function test_point_of_sale_lists_products_in_stock_and_keeps_the_cart_after_an_error(): void
    {
        $available = Product::factory()->create(['nom' => 'Clou galvanisé', 'quantite_stock' => 8]);
        Product::factory()->create(['nom' => 'Produit épuisé', 'quantite_stock' => 0]);

        $this->withSession(['_old_input' => ['items' => [['product_id' => $available->id, 'quantite' => 2]]]])
            ->get(route('ventes.create'))
            ->assertOk()
            ->assertSee('data-pos-form', false)
            ->assertSee('Clou galvanisé')
            ->assertSee($available->category->nom)
            ->assertDontSee('Produit épuisé')
            ->assertSee('data-pos-initial-items="[{&quot;product_id&quot;:'.$available->id, false);
    }

    public function test_registered_customer_can_pay_partially_and_settle_later(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['prix_vente' => 2500, 'quantite_stock' => 4]);

        $this->post(route('ventes.store'), [...$this->saleData($product, 2, 2000), 'customer_id' => $customer->id])
            ->assertSessionHasNoErrors();

        $sale = Sale::query()->firstOrFail();
        $this->assertSame(Sale::STATUS_PARTIALLY_PAID, $sale->statut);

        $this->post(route('ventes.paiements.store', $sale), ['montant' => 3000, 'moyen_paiement' => 'especes'])
            ->assertSessionHasNoErrors();
        $this->assertSame(Sale::STATUS_PAID, $sale->fresh()->statut);
    }

    private function saleData(Product $product, float $quantity, float $amountReceived): array
    {
        return [
            'client_nom' => '',
            'moyen_paiement' => 'especes',
            'montant_recu' => $amountReceived,
            'items' => [
                ['product_id' => $product->id, 'quantite' => $quantity],
            ],
        ];
    }
}
