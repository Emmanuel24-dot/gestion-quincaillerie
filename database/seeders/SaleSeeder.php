<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Sale::query()->where('numero', 'DEMO-VENTE-001')->exists()) {
            return;
        }

        $productId = Product::query()
            ->where('statut', true)
            ->where('quantite_stock', '>', 0)
            ->where('prix_vente', '>', 0)
            ->orderBy('id')
            ->value('id');

        if (! $productId) {
            return;
        }

        DB::transaction(function () use ($productId): void {
            /** @var Product|null $product */
            $product = Product::query()->with('unit')->lockForUpdate()->find($productId);
            if (! $product || ! $product->statut || (float) $product->quantite_stock <= 0) {
                return;
            }

            $quantity = min(1, (float) $product->quantite_stock);
            $stockBefore = (float) $product->quantite_stock;
            $stockAfter = round($stockBefore - $quantity, 3);
            $total = round($quantity * (float) $product->prix_vente, 2);

            $sale = Sale::create([
                'numero' => 'DEMO-VENTE-001',
                'date_vente' => now(),
                'client_nom' => 'Client de démonstration',
                'moyen_paiement' => 'especes',
                'statut' => 'payee',
                'sous_total' => $total,
                'total' => $total,
                'montant_recu' => $total,
                'monnaie_rendue' => 0,
            ]);

            $sale->items()->create([
                'product_id' => $product->id,
                'reference_produit' => $product->reference,
                'nom_produit' => $product->nom,
                'unite_symbole' => $product->unit->symbole,
                'quantite' => $quantity,
                'prix_unitaire' => $product->prix_vente,
                'total_ligne' => $total,
            ]);

            $product->update(['quantite_stock' => $stockAfter]);
            $product->stockMovements()->create([
                'type' => 'sortie',
                'quantite' => $quantity,
                'stock_avant' => $stockBefore,
                'stock_apres' => $stockAfter,
                'motif' => "Vente {$sale->numero}",
                'notes' => 'Donnée de démonstration créée par SaleSeeder.',
            ]);
        }, 3);
    }
}
