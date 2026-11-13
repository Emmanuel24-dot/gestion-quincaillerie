<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $purchases = Purchase::query()
            ->with('supplier')
            ->withCount('items')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('numero', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('nom', 'like', "%{$search}%"));
                });
            })
            ->when($filters['date_debut'] ?? null, fn ($query, string $date) => $query->whereDate('date_achat', '>=', $date))
            ->when($filters['date_fin'] ?? null, fn ($query, string $date) => $query->whereDate('date_achat', '<=', $date))
            ->latest('date_achat')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create(): View
    {
        $suppliers = Supplier::query()->where('statut', true)->orderBy('nom')->get();
        $products = Product::query()->with('unit')->where('statut', true)->orderBy('nom')->get();
        $paymentMethods = $this->paymentMethods();

        return view('purchases.create', compact('suppliers', 'products', 'paymentMethods'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'montant_paye' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'moyen_paiement' => ['nullable', 'in:especes,mobile_money,carte,autre'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*' => ['required', 'array:product_id,quantite,prix_unitaire'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantite' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:999999999.999'],
            'items.*.prix_unitaire' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
        ]);

        $purchase = DB::transaction(function () use ($data): Purchase {
            $supplier = Supplier::query()->lockForUpdate()->find($data['supplier_id']);
            if (! $supplier || ! $supplier->statut) {
                throw ValidationException::withMessages([
                    'supplier_id' => 'Ce fournisseur n’est plus actif.',
                ]);
            }

            $productIds = collect($data['items'])
                ->pluck('product_id')
                ->map(fn ($productId): int => (int) $productId)
                ->sort()
                ->values();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->with('unit')
                ->get()
                ->keyBy('id');

            $purchaseLines = [];
            $subtotal = 0.0;

            foreach ($data['items'] as $index => $item) {
                $product = $products->get((int) $item['product_id']);
                if (! $product || ! $product->statut) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => 'Ce produit n’est plus actif.',
                    ]);
                }

                $quantity = (float) $item['quantite'];
                $unitPrice = (float) $item['prix_unitaire'];
                $lineTotal = round($quantity * $unitPrice, 2);
                $subtotal = round($subtotal + $lineTotal, 2);
                $purchaseLines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'stock_before' => (float) $product->quantite_stock,
                ];
            }

            if (isset($data['montant_paye']) && (float) $data['montant_paye'] > $subtotal) {
                throw ValidationException::withMessages(['montant_paye' => 'Le règlement ne peut pas dépasser le total de l’achat.']);
            }

            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'numero' => 'TEMP-'.Str::uuid(),
                'date_achat' => now(),
                'statut' => 'receptionnee',
                'sous_total' => $subtotal,
                'total' => $subtotal,
                'montant_paye' => min((float) ($data['montant_paye'] ?? $subtotal), $subtotal),
                'moyen_paiement' => $data['moyen_paiement'] ?? 'autre',
            ]);
            $purchase->update([
                'numero' => sprintf('ACH-%s-%06d', now()->format('Ymd'), $purchase->id),
            ]);
            if ((float) $purchase->montant_paye > 0) {
                $purchase->payments()->create([
                    'montant' => $purchase->montant_paye,
                    'moyen_paiement' => $purchase->moyen_paiement,
                    'date_paiement' => now(),
                ]);
            }

            foreach ($purchaseLines as $purchaseLine) {
                $product = $purchaseLine['product'];
                $stockAfter = round($purchaseLine['stock_before'] + $purchaseLine['quantity'], 3);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'reference_produit' => $product->reference,
                    'nom_produit' => $product->nom,
                    'unite_symbole' => $product->unit->symbole,
                    'quantite' => $purchaseLine['quantity'],
                    'prix_unitaire' => $purchaseLine['unit_price'],
                    'total_ligne' => $purchaseLine['line_total'],
                ]);

                $product->update([
                    'quantite_stock' => $stockAfter,
                    'prix_achat' => $purchaseLine['unit_price'],
                ]);
                $product->stockMovements()->create([
                    'type' => 'entree',
                    'quantite' => $purchaseLine['quantity'],
                    'stock_avant' => $purchaseLine['stock_before'],
                    'stock_apres' => $stockAfter,
                    'motif' => "Achat {$purchase->numero}",
                    'notes' => "Réception auprès du fournisseur {$supplier->nom}.",
                ]);
            }

            return $purchase;
        }, 3);

        return redirect()->route('achats.show', $purchase)->with('success', 'L’achat a été enregistré et le stock a été mis à jour.');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'items', 'payments']);
        $paymentMethods = $this->paymentMethods();

        return view('purchases.show', compact('purchase', 'paymentMethods'));
    }

    /**
     * @return array<string, string>
     */
    private function paymentMethods(): array
    {
        return [
            'especes' => 'Espèces',
            'mobile_money' => 'Mobile Money',
            'carte' => 'Carte bancaire',
            'autre' => 'Autre',
        ];
    }
}
