<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_sale_can_be_invoiced_only_once(): void
    {
        $sale = Sale::factory()->create();
        $sale->items()->create([
            'product_id' => Product::factory()->create()->id,
            'reference_produit' => 'PRD-001',
            'nom_produit' => 'Marteau',
            'unite_symbole' => 'pce',
            'quantite' => 2,
            'prix_unitaire' => 1500,
            'total_ligne' => 3000,
        ]);

        $this->post(route('factures.store', $sale))
            ->assertRedirect();

        $invoice = Invoice::query()->where('sale_id', $sale->id)->firstOrFail();
        $this->assertMatchesRegularExpression('/^FAC-\d{8}-\d{6,}$/', $invoice->numero);
        $this->assertSame('emise', $invoice->statut);

        $this->post(route('factures.store', $sale))
            ->assertRedirect(route('factures.show', $invoice));

        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_invoice_list_and_printable_invoice_use_the_sale_snapshots(): void
    {
        $sale = Sale::factory()->create([
            'numero' => 'VTE-20261005-000045',
            'client_nom' => 'Awa Diop',
        ]);
        $sale->items()->create([
            'product_id' => Product::factory()->create()->id,
            'reference_produit' => 'PE-010',
            'nom_produit' => 'Peinture blanche',
            'unite_symbole' => 'L',
            'quantite' => 2,
            'prix_unitaire' => 3500,
            'total_ligne' => 7000,
        ]);

        $this->get(route('factures.index'))
            ->assertOk()
            ->assertSee('VTE-20261005-000045')
            ->assertSee('À facturer');

        $this->post(route('factures.store', $sale));
        $invoice = Invoice::query()->where('sale_id', $sale->id)->firstOrFail();

        $this->get(route('factures.index', ['facturation' => 'emise']))
            ->assertOk()
            ->assertSee($invoice->numero)
            ->assertSee('Awa Diop');

        $this->get(route('factures.show', $invoice))
            ->assertOk()
            ->assertSee($invoice->numero)
            ->assertSee('Peinture blanche')
            ->assertSee('Imprimer la facture');
    }

    public function test_only_paid_sales_can_be_invoiced(): void
    {
        $sale = Sale::factory()->create(['statut' => 'annulee']);

        $this->from(route('ventes.show', $sale))
            ->post(route('factures.store', $sale))
            ->assertRedirect(route('ventes.show', $sale))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('invoices', 0);
    }
}
