<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_live_sales_customer_product_and_stock_metrics(): void
    {
        $customer = Customer::factory()->create(['nom' => 'Awa Diop']);
        $product = Product::factory()->create([
            'nom' => 'Perceuse sans fil',
            'quantite_stock' => 1,
            'seuil_alerte' => 3,
        ]);
        $sale = Sale::factory()->create([
            'customer_id' => $customer->id,
            'client_nom' => $customer->nom,
            'date_vente' => now(),
            'total' => 12500,
        ]);
        $sale->items()->create([
            'product_id' => $product->id,
            'reference_produit' => $product->reference,
            'nom_produit' => $product->nom,
            'unite_symbole' => $product->unit->symbole,
            'quantite' => 1,
            'prix_unitaire' => 12500,
            'total_ligne' => 12500,
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('12 500')
            ->assertSee('Awa Diop')
            ->assertSee('Perceuse sans fil');
    }

    public function test_sales_and_purchase_reports_follow_the_selected_date_range(): void
    {
        $customer = Customer::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['nom' => 'Marteau de charpentier']);
        $sale = Sale::factory()->create([
            'customer_id' => $customer->id,
            'date_vente' => now()->setTime(10, 0),
            'total' => 9000,
            'moyen_paiement' => 'mobile_money',
        ]);
        $sale->items()->create([
            'product_id' => $product->id,
            'reference_produit' => $product->reference,
            'nom_produit' => $product->nom,
            'unite_symbole' => $product->unit->symbole,
            'quantite' => 2,
            'prix_unitaire' => 4500,
            'total_ligne' => 9000,
        ]);
        $sale->payments()->create([
            'montant' => 9000,
            'moyen_paiement' => 'mobile_money',
            'date_paiement' => now()->setTime(10, 0),
        ]);
        Purchase::factory()->for($supplier)->create([
            'date_achat' => now()->setTime(11, 0),
            'total' => 3500,
        ]);

        $this->get(route('statistiques', [
            'date_debut' => now()->toDateString(),
            'date_fin' => now()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('9 000 FCFA')
            ->assertSee('3 500 FCFA')
            ->assertSee('Marteau de charpentier')
            ->assertSee('Mobile Money');
    }

    public function test_dashboard_and_report_show_helpful_empty_states(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Aucune vente enregistrée')
            ->assertSee('Stock à niveau');

        $this->get(route('statistiques'))
            ->assertOk()
            ->assertSee('Aucune activité sur cette période')
            ->assertSee('Aucun paiement enregistré sur cette période.');
    }
}
