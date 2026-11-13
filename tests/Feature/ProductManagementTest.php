<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    private const PRODUCT_NAME = 'Perceuse électrique';

    use RefreshDatabase;

    public function test_products_can_be_searched_and_filtered_by_category(): void
    {
        $category = Category::factory()->create(['nom' => 'Outillage']);
        Product::factory()->create(['category_id' => $category->id, 'reference' => 'OUT-001', 'nom' => 'Marteau rouge']);
        Product::factory()->create(['reference' => 'ELE-001', 'nom' => 'Lampe torche']);

        $this->get(route('produits.index', ['search' => 'OUT-001', 'category_id' => $category->id]))
            ->assertOk()
            ->assertSee('OUT-001')
            ->assertSee('Marteau rouge')
            ->assertDontSee('Lampe torche');
    }

    public function test_product_reference_is_generated_and_client_value_is_ignored(): void
    {
        $category = Category::factory()->create();
        $data = $this->productData($category, [
            'reference' => 'REFERENCE-SAISIE',
        ]);

        $this->post(route('produits.store'), $data)
            ->assertRedirect(route('produits.index'))
            ->assertSessionHas('success');

        $firstProduct = Product::query()->where('nom', self::PRODUCT_NAME)->firstOrFail();
        $this->assertMatchesRegularExpression('/^PRD-\d{6}(?:-\d{2,})?$/', $firstProduct->reference);
        $this->assertNotSame('REFERENCE-SAISIE', $firstProduct->reference);

        $this->post(route('produits.store'), $data)
            ->assertRedirect(route('produits.index'));

        $secondProduct = Product::query()->whereKeyNot($firstProduct->id)->where('nom', self::PRODUCT_NAME)->firstOrFail();
        $this->assertNotSame($firstProduct->reference, $secondProduct->reference);
        $this->assertDatabaseCount('products', 2);
    }

    public function test_product_can_be_updated_and_deleted(): void
    {
        $product = Product::factory()->create(['reference' => 'PRD-200']);
        $category = $product->category;
        $stockBeforeUpdate = $product->quantite_stock;

        $this->put(route('produits.update', $product), $this->productData($category, [
            'nom' => 'Perceuse sans fil',
            'reference' => 'REFERENCE-MODIFIEE',
            'quantite_stock' => 8,
        ]))
            ->assertRedirect(route('produits.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'nom' => 'Perceuse sans fil',
            'reference' => $product->reference,
            'quantite_stock' => $stockBeforeUpdate,
        ]);

        $this->delete(route('produits.destroy', $product))
            ->assertRedirect(route('produits.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $this->delete('/categories/'.$category->id)
            ->assertRedirect('/categories')
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_product_forms_render_and_negative_stock_is_rejected(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->get(route('produits.create'))
            ->assertOk()
            ->assertSee('Nouveau produit');

        $this->get(route('produits.edit', $product))
            ->assertOk()
            ->assertSee($product->reference);

        $this->from(route('produits.create'))
            ->post(route('produits.store'), $this->productData($category, [
                'quantite_stock' => -1,
            ]))
            ->assertSessionHasErrors('quantite_stock');

        $this->assertDatabaseCount('products', 1);
    }

    private function productData(Category $category, array $overrides = []): array
    {
        return array_merge([
            'nom' => self::PRODUCT_NAME,
            'description' => 'Perceuse filaire 750 W',
            'category_id' => $category->id,
            'unit_id' => Unit::factory()->create()->id,
            'prix_achat' => 25000,
            'prix_vente' => 35000,
            'quantite_stock' => 5,
            'seuil_alerte' => 2,
            'statut' => 1,
        ], $overrides);
    }
}
