<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $supplier = Supplier::query()->first() ?? Supplier::factory()->create();
            $product = Product::query()->first() ?? Product::factory()->create();
            $quantity = 5.0;
            $unitPrice = (float) $product->prix_achat;
            $lineTotal = round($quantity * $unitPrice, 2);
            $stockBefore = (float) $product->quantite_stock;
            $stockAfter = round($stockBefore + $quantity, 3);

            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'numero' => 'TEMP-'.Str::uuid(),
                'date_achat' => now(),
                'statut' => 'receptionnee',
                'sous_total' => $lineTotal,
                'total' => $lineTotal,
            ]);
            $purchase->update([
                'numero' => sprintf('ACH-%s-%06d', now()->format('Ymd'), $purchase->id),
            ]);
            $purchase->items()->create([
                'product_id' => $product->id,
                'reference_produit' => $product->reference,
                'nom_produit' => $product->nom,
                'unite_symbole' => $product->unit->symbole,
                'quantite' => $quantity,
                'prix_unitaire' => $unitPrice,
                'total_ligne' => $lineTotal,
            ]);
            $product->update(['quantite_stock' => $stockAfter]);
            $product->stockMovements()->create([
                'type' => 'entree',
                'quantite' => $quantity,
                'stock_avant' => $stockBefore,
                'stock_apres' => $stockAfter,
                'motif' => "Achat {$purchase->numero}",
                'notes' => "Réception auprès du fournisseur {$supplier->nom}.",
            ]);
        });
    }
}
