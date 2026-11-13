<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_can_be_exported_as_csv(): void
    {
        Sale::factory()->create(['date_vente' => now(), 'total' => 4500, 'numero' => 'VTE-EXPORT-1']);

        $response = $this->get(route('rapports.export'));

        $response->assertOk()->assertDownload('rapport-'.now()->format('Y-m-d').'.csv');
        $this->assertStringContainsString('VTE-EXPORT-1', $response->streamedContent());
    }

    public function test_categories_can_be_created_updated_and_deleted(): void
    {
        $this->post(route('categories.store'), ['nom' => 'Plomberie', 'description' => 'Tuyaux', 'statut' => '1'])
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');

        $category = Category::query()->where('nom', 'Plomberie')->firstOrFail();

        $this->put(route('categories.update', $category), ['nom' => 'Plomberie', 'statut' => '0'])
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');
        $this->assertFalse($category->fresh()->statut);

        $this->get(route('categories.index'))->assertOk()->assertSee('Plomberie')->assertSee('Inactive');

        $this->delete(route('categories.destroy', $category))->assertSessionHas('success');
        $this->assertModelMissing($category);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $product = Product::factory()->create();

        $this->delete(route('categories.destroy', $product->category))->assertSessionHas('error');
        $this->assertModelExists($product->category);
    }

    public function test_user_pages_render_and_hide_self_deletion(): void
    {
        $seller = User::factory()->create(['name' => 'Moussa Vendeur']);

        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee('Moussa Vendeur')
            ->assertSee('(vous)')
            ->assertSee('action="'.route('users.destroy', $seller).'"', false)
            ->assertDontSee('action="'.route('users.destroy', auth()->user()).'"', false);
        $this->get(route('users.create'))->assertOk();
        $this->get(route('users.edit', $seller))->assertOk()->assertSee('Moussa Vendeur');
    }

    public function test_pages_start_with_the_doctype_and_use_bootstrap_pagination(): void
    {
        Customer::factory()->count(16)->create();

        $response = $this->get(route('clients.index'));

        $response->assertOk()->assertSee('page-link', false);
        $this->assertStringStartsWith('<!DOCTYPE html>', $response->getContent());
    }

    public function test_setup_page_renders_when_no_account_exists(): void
    {
        auth()->logout();
        User::query()->delete();

        $response = $this->get(route('setup'));

        $response->assertOk()->assertSee('Créer l’administrateur');
        $this->assertStringStartsWith('<!doctype html>', $response->getContent());
    }

    public function test_redesigned_detail_pages_render(): void
    {
        $customer = Customer::factory()->create(['nom' => 'Awa Diop']);
        $product = Product::factory()->create(['prix_vente' => 1000, 'quantite_stock' => 10, 'seuil_alerte' => 20]);
        $supplier = Supplier::factory()->create(['nom' => 'Sococim']);

        $this->post(route('ventes.store'), [
            'customer_id' => $customer->id,
            'moyen_paiement' => 'mobile_money',
            'montant_recu' => 1500,
            'items' => [['product_id' => $product->id, 'quantite' => 3]],
        ])->assertSessionHasNoErrors();
        $sale = Sale::query()->with('items')->firstOrFail();
        $this->post(route('ventes.retours.store', $sale), [
            'motif' => 'Casse',
            'moyen_remboursement' => 'especes',
            'items' => [['sale_item_id' => $sale->items->first()->id, 'quantite' => 1]],
        ])->assertSessionHasNoErrors();

        $this->get(route('ventes.show', $sale))->assertOk()->assertSee('Règlement')->assertSee('Awa Diop')->assertSee('Mobile Money')->assertSee('Retours');

        $this->post(route('achats.store'), [
            'supplier_id' => $supplier->id,
            'montant_paye' => 100,
            'moyen_paiement' => 'carte',
            'items' => [['product_id' => $product->id, 'quantite' => 2, 'prix_unitaire' => 500]],
        ])->assertSessionHasNoErrors();
        $purchase = Purchase::query()->firstOrFail();

        $this->get(route('achats.show', $purchase))->assertOk()->assertSee('Sococim')->assertSee('Carte bancaire')->assertSee('Solde dû');
        $this->get(route('fournisseurs.show', $supplier))->assertOk()->assertSee($purchase->numero);
        $this->get(route('clients.show', $customer))->assertOk()->assertSee($sale->numero);
        $this->get(route('stock.index', ['niveau' => 'faible']))->assertOk()->assertSee('stock-meter', false);
        $this->get(route('ventes.create', ['customer_id' => $customer->id]))->assertOk()->assertSee('value="'.$customer->id.'" selected', false);
    }
}
