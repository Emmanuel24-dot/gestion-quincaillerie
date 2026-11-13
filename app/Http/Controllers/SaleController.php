<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $sales = Sale::query()
            ->withCount('items')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('numero', 'like', "%{$search}%")
                        ->orWhere('client_nom', 'like', "%{$search}%");
                });
            })
            ->when($filters['date_debut'] ?? null, fn ($query, string $date) => $query->whereDate('date_vente', '>=', $date))
            ->when($filters['date_fin'] ?? null, fn ($query, string $date) => $query->whereDate('date_vente', '<=', $date))
            ->latest('date_vente')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $products = Product::query()
            ->with(['unit', 'category'])
            ->where('statut', true)
            ->where('quantite_stock', '>', 0)
            ->orderBy('nom')
            ->get();
        $categories = $products->pluck('category')->filter()->unique('id')->sortBy('nom')->values();
        $paymentMethods = $this->paymentMethods();
        $customers = Customer::query()->where('statut', true)->orderBy('nom')->get();

        return view('sales.create', compact('products', 'categories', 'paymentMethods', 'customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'client_nom' => ['nullable', 'string', 'max:150'],
            'moyen_paiement' => ['required', Rule::in(array_keys($this->paymentMethods()))],
            'montant_recu' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*' => ['required', 'array:product_id,quantite'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantite' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:999999999.999'],
        ]);

        $sale = DB::transaction(function () use ($data): Sale {
            $customer = null;
            if (! empty($data['customer_id'])) {
                $customer = Customer::query()->lockForUpdate()->find($data['customer_id']);

                if (! $customer || ! $customer->statut) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'Ce client n’est plus actif.',
                    ]);
                }
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

            $saleLines = [];
            $subtotal = 0.0;

            foreach ($data['items'] as $index => $item) {
                $product = $products->get((int) $item['product_id']);
                $quantity = (float) $item['quantite'];

                if (! $product || ! $product->statut) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => 'Ce produit n’est plus disponible à la vente.',
                    ]);
                }

                if ($quantity > (float) $product->quantite_stock) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantite" => "Le stock disponible pour {$product->nom} est de {$product->quantite_stock} {$product->unit->symbole}.",
                    ]);
                }

                $unitPrice = (float) $product->prix_vente;
                $lineTotal = round($quantity * $unitPrice, 2);
                $subtotal = round($subtotal + $lineTotal, 2);
                $saleLines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'stock_before' => (float) $product->quantite_stock,
                    'unit_cost' => (float) $product->prix_achat,
                ];
            }

            $amountReceived = (float) $data['montant_recu'];
            if ($amountReceived < $subtotal && empty($data['customer_id'])) {
                throw ValidationException::withMessages([
                    'customer_id' => 'Un client enregistré est obligatoire pour enregistrer un paiement partiel.',
                ]);
            }
            $paidAmount = min($amountReceived, $subtotal);

            $sale = Sale::create([
                'numero' => 'TEMP-'.Str::uuid(),
                'date_vente' => now(),
                'client_nom' => $customer?->nom ?? ($data['client_nom'] ?? null),
                'customer_id' => $customer?->id,
                'moyen_paiement' => $data['moyen_paiement'],
                'statut' => match (true) {
                    $paidAmount >= $subtotal => Sale::STATUS_PAID,
                    $paidAmount > 0 => Sale::STATUS_PARTIALLY_PAID,
                    default => Sale::STATUS_UNPAID,
                },
                'sous_total' => $subtotal,
                'total' => $subtotal,
                'montant_recu' => $amountReceived,
                'montant_paye' => $paidAmount,
                'monnaie_rendue' => round(max($amountReceived - $subtotal, 0), 2),
            ]);
            if ($paidAmount > 0) {
                $sale->payments()->create([
                    'montant' => $paidAmount,
                    'moyen_paiement' => $data['moyen_paiement'],
                    'date_paiement' => now(),
                ]);
            }
            $sale->update([
                'numero' => sprintf('VTE-%s-%06d', now()->format('Ymd'), $sale->id),
            ]);

            foreach ($saleLines as $saleLine) {
                $product = $saleLine['product'];
                $stockAfter = round($saleLine['stock_before'] - $saleLine['quantity'], 3);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'reference_produit' => $product->reference,
                    'nom_produit' => $product->nom,
                    'unite_symbole' => $product->unit->symbole,
                    'quantite' => $saleLine['quantity'],
                    'prix_unitaire' => $saleLine['unit_price'],
                    'prix_achat_unitaire' => $saleLine['unit_cost'],
                    'total_ligne' => $saleLine['line_total'],
                ]);

                $product->update(['quantite_stock' => $stockAfter]);
                $product->stockMovements()->create([
                    'type' => 'sortie',
                    'quantite' => $saleLine['quantity'],
                    'stock_avant' => $saleLine['stock_before'],
                    'stock_apres' => $stockAfter,
                    'motif' => "Vente {$sale->numero}",
                    'notes' => 'Sortie de stock liée à une vente.',
                ]);
            }

            return $sale;
        }, 3);

        return redirect()->route('ventes.show', $sale)->with('success', 'La vente a été enregistrée.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale): View
    {
        $sale->load(['items.returns', 'invoice', 'payments', 'returns.items', 'customer']);
        $paymentMethods = $this->paymentMethods();

        return view('sales.show', compact('sale', 'paymentMethods'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    /**
     * Update the specified resource in storage.
     */
    /**
     * Remove the specified resource from storage.
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
