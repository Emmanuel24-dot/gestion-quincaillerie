<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_product_stock_is_recorded_in_the_movement_history(): void
    {
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $this->post(route('produits.store'), [
            'nom' => 'Ciment 25 kg',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'prix_achat' => 2000,
            'prix_vente' => 2500,
            'quantite_stock' => 12,
            'seuil_alerte' => 3,
            'statut' => 1,
        ])->assertRedirect(route('produits.index'));

        $product = Product::query()->where('nom', 'Ciment 25 kg')->firstOrFail();
        $movement = $product->stockMovements()->firstOrFail();

        $this->assertMatchesRegularExpression('/^PRD-\d{6}(?:-\d{2,})?$/', $product->reference);
        $this->assertSame('stock_initial', $movement->type);
        $this->assertSame('12.000', $movement->quantite);
        $this->assertSame('0.000', $movement->stock_avant);
        $this->assertSame('12.000', $movement->stock_apres);
    }

    public function test_entries_and_exits_update_stock_and_keep_a_snapshot(): void
    {
        $product = $this->productWithStock(5);

        $this->post(route('mouvements-stock.store'), $this->movementData($product, 'entree', 3))
            ->assertRedirect(route('mouvements-stock.index'))
            ->assertSessionHas('success');

        $this->post(route('mouvements-stock.store'), $this->movementData($product, 'sortie', 2))
            ->assertRedirect(route('mouvements-stock.index'))
            ->assertSessionHas('success');

        $this->assertSame('6.000', $product->fresh()->quantite_stock);

        $outgoingMovement = $product->stockMovements()->where('type', 'sortie')->firstOrFail();
        $this->assertSame('2.000', $outgoingMovement->quantite);
        $this->assertSame('8.000', $outgoingMovement->stock_avant);
        $this->assertSame('6.000', $outgoingMovement->stock_apres);
    }

    public function test_adjustments_can_increase_or_decrease_stock(): void
    {
        $product = $this->productWithStock(4);

        $this->post(route('mouvements-stock.store'), $this->movementData($product, 'ajustement_positif', 3))
            ->assertSessionHas('success');
        $this->post(route('mouvements-stock.store'), $this->movementData($product, 'ajustement_negatif', 2))
            ->assertSessionHas('success');

        $this->assertSame('5.000', $product->fresh()->quantite_stock);
        $this->assertDatabaseCount('stock_movements', 3);
    }

    public function test_stock_cannot_be_reduced_below_zero(): void
    {
        $product = $this->productWithStock(2);

        $this->from(route('mouvements-stock.create'))
            ->post(route('mouvements-stock.store'), $this->movementData($product, 'sortie', 3))
            ->assertSessionHasErrors('quantite');

        $this->assertSame('2.000', $product->fresh()->quantite_stock);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_stock_and_movement_pages_are_available_and_filtered(): void
    {
        $lowStockProduct = $this->productWithStock(2, ['seuil_alerte' => 3, 'reference' => 'LOW-001']);
        $this->productWithStock(12, ['seuil_alerte' => 3, 'reference' => 'OK-001']);
        $lowStockProduct->stockMovements()->create([
            'type' => 'entree',
            'quantite' => 1,
            'stock_avant' => 1,
            'stock_apres' => 2,
            'motif' => 'Réception',
        ]);

        $this->get(route('stock.index', ['niveau' => 'faible']))
            ->assertOk()
            ->assertSee('LOW-001')
            ->assertDontSee('OK-001');

        $this->get(route('mouvements-stock.index', ['search' => 'LOW-001']))
            ->assertOk()
            ->assertSee('Réception')
            ->assertDontSee('OK-001');

        $this->get(route('mouvements-stock.create'))
            ->assertOk()
            ->assertSee('Nouveau mouvement de stock');
    }

    public function test_products_with_stock_history_cannot_be_deleted(): void
    {
        $product = $this->productWithStock(1);

        $this->delete(route('produits.destroy', $product))
            ->assertRedirect(route('produits.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($product);
    }

    private function productWithStock(float $stock, array $overrides = []): Product
    {
        $product = Product::factory()->create(array_merge([
            'quantite_stock' => $stock,
            'seuil_alerte' => 3,
        ], $overrides));

        if ($stock > 0) {
            $product->stockMovements()->create([
                'type' => 'stock_initial',
                'quantite' => $stock,
                'stock_avant' => 0,
                'stock_apres' => $stock,
                'motif' => 'Stock initial',
            ]);
        }

        return $product;
    }

    private function movementData(Product $product, string $type, float $quantity): array
    {
        return [
            'product_id' => $product->id,
            'type' => $type,
            'quantite' => $quantity,
            'motif' => 'Test mouvement',
        ];
    }
}
