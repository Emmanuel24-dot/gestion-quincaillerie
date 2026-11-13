<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    public function inventory(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'stock_compte' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:999999999.999'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data, $product): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $stockBefore = (float) $lockedProduct->quantite_stock;
            $stockAfter = (float) $data['stock_compte'];
            $difference = round(abs($stockAfter - $stockBefore), 3);

            if ($difference === 0.0) {
                return;
            }

            $lockedProduct->update(['quantite_stock' => $stockAfter]);
            $lockedProduct->stockMovements()->create([
                'type' => $stockAfter > $stockBefore ? 'ajustement_positif' : 'ajustement_negatif',
                'quantite' => $difference,
                'stock_avant' => $stockBefore,
                'stock_apres' => $stockAfter,
                'motif' => 'Inventaire physique',
                'notes' => $data['notes'] ?? 'Comptage physique du stock.',
            ]);
        }, 3);

        return back()->with('success', 'Le comptage physique a été enregistré.');
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(array_keys(StockMovement::TYPE_LABELS))],
        ]);

        $movements = StockMovement::query()
            ->with('product.unit')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->whereHas('product', function (Builder $query) use ($search): void {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('nom', 'like', "%{$search}%");
                });
            })
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $types = StockMovement::TYPE_LABELS;

        return view('stock-movements.index', compact('movements', 'types'));
    }

    public function create(Request $request): View
    {
        $products = Product::query()->with('unit')->where('statut', true)->orderBy('nom')->get();
        $selectedProductId = $request->integer('product_id');
        $types = [
            'entree' => StockMovement::TYPE_LABELS['entree'],
            'sortie' => StockMovement::TYPE_LABELS['sortie'],
            'ajustement_positif' => StockMovement::TYPE_LABELS['ajustement_positif'],
            'ajustement_negatif' => StockMovement::TYPE_LABELS['ajustement_negatif'],
        ];

        return view('stock-movements.create', compact('products', 'selectedProductId', 'types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', Rule::in(['entree', 'sortie', 'ajustement_positif', 'ajustement_negatif'])],
            'quantite' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:999999999.999'],
            'motif' => ['required', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data): void {
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);
            $stockBefore = (float) $product->quantite_stock;
            $quantity = (float) $data['quantite'];
            $increasesStock = in_array($data['type'], ['entree', 'ajustement_positif'], true);
            $stockAfter = round($stockBefore + ($increasesStock ? $quantity : -$quantity), 3);

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'quantite' => "La quantité demandée dépasse le stock disponible ({$product->quantite_stock} {$product->unit->symbole}).",
                ]);
            }

            $product->update(['quantite_stock' => $stockAfter]);
            $product->stockMovements()->create([
                'type' => $data['type'],
                'quantite' => $quantity,
                'stock_avant' => $stockBefore,
                'stock_apres' => $stockAfter,
                'motif' => $data['motif'],
                'notes' => $data['notes'] ?? null,
            ]);
        }, 3);

        return redirect()->route('mouvements-stock.index')->with('success', 'Le mouvement de stock a été enregistré.');
    }
}
