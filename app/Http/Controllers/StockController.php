<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'niveau' => ['nullable', 'in:normal,faible,rupture'],
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
            ->when(($filters['niveau'] ?? null) === 'rupture', fn ($query) => $query->where('quantite_stock', '<=', 0))
            ->when(($filters['niveau'] ?? null) === 'faible', fn ($query) => $query->where('quantite_stock', '>', 0)->whereColumn('quantite_stock', '<=', 'seuil_alerte'))
            ->when(($filters['niveau'] ?? null) === 'normal', fn ($query) => $query->whereColumn('quantite_stock', '>', 'seuil_alerte'))
            ->orderBy('quantite_stock')
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::query()->orderBy('nom')->get();
        $productCount = Product::count();
        $outOfStockCount = Product::where('quantite_stock', '<=', 0)->count();
        $lowStockCount = Product::where('quantite_stock', '>', 0)
            ->whereColumn('quantite_stock', '<=', 'seuil_alerte')
            ->count();

        return view('stock.index', compact('products', 'categories', 'productCount', 'outOfStockCount', 'lowStockCount'));
    }
}
