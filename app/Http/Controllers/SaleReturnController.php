<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleReturnController extends Controller
{
    public function store(Request $request, Sale $sale): RedirectResponse
    {
        $data = $request->validate([
            'motif' => ['required', 'string', 'max:255'],
            'moyen_remboursement' => ['required', 'in:especes,mobile_money,carte,autre'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*' => ['required', 'array:sale_item_id,quantite'],
            'items.*.sale_item_id' => ['required', 'integer', 'distinct', 'exists:sale_items,id'],
            'items.*.quantite' => ['required', 'numeric', 'decimal:0,3', 'gt:0', 'max:999999999.999'],
        ]);

        DB::transaction(function () use ($sale, $data): void {
            $lockedSale = Sale::query()->lockForUpdate()->findOrFail($sale->id);
            $saleItems = $lockedSale->items()->whereIn('id', collect($data['items'])->pluck('sale_item_id'))->get()->keyBy('id');
            $returnLines = [];
            $returnTotal = 0.0;

            foreach ($data['items'] as $index => $item) {
                $saleItem = $saleItems->get((int) $item['sale_item_id']);
                if (! $saleItem) {
                    throw ValidationException::withMessages(["items.{$index}.sale_item_id" => 'Cet article ne fait pas partie de la vente.']);
                }

                $previouslyReturned = (float) $saleItem->returns()->sum('quantite');
                $quantity = (float) $item['quantite'];
                if ($quantity > (float) $saleItem->quantite - $previouslyReturned) {
                    throw ValidationException::withMessages(["items.{$index}.quantite" => 'La quantité retournée dépasse la quantité encore vendable.']);
                }

                $lineTotal = round($quantity * (float) $saleItem->prix_unitaire, 2);
                $returnTotal = round($returnTotal + $lineTotal, 2);
                $returnLines[] = ['sale_item' => $saleItem, 'quantity' => $quantity, 'total' => $lineTotal];
            }

            $alreadyRefunded = (float) $lockedSale->returns()->sum('total');
            if ($returnTotal > (float) $lockedSale->montant_paye - $alreadyRefunded) {
                throw ValidationException::withMessages(['items' => 'Le remboursement dépasse le montant déjà encaissé sur cette vente.']);
            }

            $saleReturn = $lockedSale->returns()->create([
                'numero' => 'TEMP-'.Str::uuid(),
                'date_retour' => now(),
                'total' => $returnTotal,
                'moyen_remboursement' => $data['moyen_remboursement'],
                'motif' => $data['motif'],
            ]);
            $saleReturn->update(['numero' => sprintf('RET-%s-%06d', now()->format('Ymd'), $saleReturn->id)]);

            foreach ($returnLines as $line) {
                $saleItem = $line['sale_item'];
                $product = Product::query()->lockForUpdate()->findOrFail($saleItem->product_id);
                $stockBefore = (float) $product->quantite_stock;
                $stockAfter = round($stockBefore + $line['quantity'], 3);
                $saleReturn->items()->create([
                    'sale_item_id' => $saleItem->id,
                    'quantite' => $line['quantity'],
                    'prix_unitaire' => $saleItem->prix_unitaire,
                    'total_ligne' => $line['total'],
                ]);
                $product->update(['quantite_stock' => $stockAfter]);
                $product->stockMovements()->create([
                    'type' => 'entree',
                    'quantite' => $line['quantity'],
                    'stock_avant' => $stockBefore,
                    'stock_apres' => $stockAfter,
                    'motif' => "Retour {$saleReturn->numero}",
                    'notes' => "Remboursement via {$data['moyen_remboursement']}. {$data['motif']}",
                ]);
            }
        }, 3);

        return back()->with('success', 'Le retour a été enregistré et le stock mis à jour.');
    }
}
