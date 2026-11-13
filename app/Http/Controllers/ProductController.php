<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'statut' => ['nullable', 'in:0,1'],
        ]);

        $products = Product::query()
            ->with(['category', 'unit'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('nom', 'like', "%{$search}%");
                });
            })
            ->when($filters['category_id'] ?? null, fn ($query, int $categoryId) => $query->where('category_id', $categoryId))
            ->when(array_key_exists('statut', $filters), fn ($query) => $query->where('statut', (bool) $filters['statut']))
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::query()->orderBy('nom')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Category::query()->orderBy('nom')->get();
        $units = Unit::query()->where('statut', true)->orderBy('nom')->get();

        return view('products.create', compact('categories', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['statut'] = $request->boolean('statut');
        DB::transaction(function () use ($data): void {
            $data['reference'] = 'TMP-'.Str::uuid();
            $product = Product::create($data);
            $product->update(['reference' => $this->generateReference($product)]);
            $initialStock = (float) $data['quantite_stock'];

            if ($initialStock > 0) {
                $product->stockMovements()->create([
                    'type' => 'stock_initial',
                    'quantite' => $initialStock,
                    'stock_avant' => 0,
                    'stock_apres' => $initialStock,
                    'motif' => 'Stock initial',
                ]);
            }
        });

        return redirect()->route('produits.index')->with('success', 'Le produit a été créé.');
    }

    /**
     * Display the specified resource.
     */
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $categories = Category::query()->orderBy('nom')->get();
        $units = Unit::query()
            ->where('statut', true)
            ->orWhereKey($product->unit_id)
            ->orderBy('nom')
            ->get();

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate($this->rules($product));
        $data['statut'] = $request->boolean('statut');
        $product->update($data);

        return redirect()->route('produits.index')->with('success', 'Le produit a été modifié.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->stockMovements()->exists()) {
            return redirect()->route('produits.index')->with('error', 'Ce produit ne peut pas être supprimé car son stock possède un historique de mouvements.');
        }

        $product->delete();

        return redirect()->route('produits.index')->with('success', 'Le produit a été supprimé.');
    }

    private function rules(?Product $product = null): array
    {
        $rules = [
            'nom' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'prix_achat' => ['required', 'numeric', 'min:0'],
            'prix_vente' => ['required', 'numeric', 'min:0'],
            'seuil_alerte' => ['required', 'numeric', 'min:0'],
            'statut' => ['sometimes', 'boolean'],
        ];

        if ($product === null) {
            $rules['quantite_stock'] = ['required', 'numeric', 'min:0', 'decimal:0,3'];
        }

        return $rules;
    }

    private function generateReference(Product $product): string
    {
        $sequence = 0;
        do {
            $suffix = $sequence === 0 ? '' : '-'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
            $reference = sprintf('PRD-%06d%s', $product->id, $suffix);
            $sequence++;
        } while (Product::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
