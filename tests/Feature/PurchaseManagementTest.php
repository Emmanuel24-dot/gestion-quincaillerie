<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_receipt_records_items_and_updates_stock_atomically(): void
    {
        $supplier = Supplier::factory()->create(['nom' => 'Fournitures Dakar']);
        $product = Product::factory()->create([
            'reference' => 'CIM-001',
            'nom' => 'Ciment 25 kg',
            'prix_achat' => 4000,
            'quantite_stock' => 3,
        ]);

        $this->post(route('achats.store'), [
            'supplier_id' => $supplier->id,
            'items' => [
                ['product_id' => $product->id, 'quantite' => 2.5, 'prix_unitaire' => 4500],
            ],
        ])->assertRedirect();

        $purchase = Purchase::query()->with('items')->firstOrFail();
        $this->assertMatchesRegularExpression('/^ACH-\d{8}-\d{6}$/', $purchase->numero);
        $this->assertSame('11250.00', $purchase->total);
        $this->assertCount(1, $purchase->items);
        $this->assertSame('5.500', $product->fresh()->quantite_stock);
        $this->assertSame('4500.00', $product->fresh()->prix_achat);
        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'reference_produit' => 'CIM-001',
            'quantite' => '2.500',
            'total_ligne' => '11250.00',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'entree',
            'quantite' => '2.500',
            'stock_avant' => '3.000',
            'stock_apres' => '5.500',
            'motif' => "Achat {$purchase->numero}",
        ]);

        $this->get(route('achats.index'))
            ->assertOk()
            ->assertSee($purchase->numero)
            ->assertSee('Fournitures Dakar');

        $this->get(route('achats.show', $purchase))
            ->assertOk()
            ->assertSee('Ciment 25 kg')
            ->assertSee('11 250 FCFA');
    }

    public function test_inactive_supplier_cannot_be_selected_for_a_purchase(): void
    {
        $supplier = Supplier::factory()->inactive()->create();
        $product = Product::factory()->create(['quantite_stock' => 5]);

        $this->from(route('achats.create'))
            ->post(route('achats.store'), [
                'supplier_id' => $supplier->id,
                'items' => [
                    ['product_id' => $product->id, 'quantite' => 1, 'prix_unitaire' => 1000],
                ],
            ])
            ->assertSessionHasErrors('supplier_id');

        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertSame('5.000', $product->fresh()->quantite_stock);
    }

    public function test_purchase_form_requires_a_supplier_and_a_line_item(): void
    {
        Supplier::factory()->create();
        Product::factory()->create();

        $this->from(route('achats.create'))
            ->post(route('achats.store'), [])
            ->assertSessionHasErrors(['supplier_id', 'items']);

        $this->assertDatabaseCount('purchases', 0);
    }
}
