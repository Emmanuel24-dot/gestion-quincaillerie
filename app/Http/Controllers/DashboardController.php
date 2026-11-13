<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();
        $salesToday = Sale::query()->whereIn('statut', Sale::REPORTABLE_STATUSES)->whereDate('date_vente', $today)->sum('total');
        $salesCountToday = Sale::query()->whereIn('statut', Sale::REPORTABLE_STATUSES)->whereDate('date_vente', $today)->count();
        $productsCount = Product::query()->where('statut', true)->count();
        $customersCount = Customer::query()->where('statut', true)->count();
        $lowStockCount = Product::query()
            ->where('statut', true)
            ->where('quantite_stock', '>', 0)
            ->whereColumn('quantite_stock', '<=', 'seuil_alerte')
            ->count();
        $recentSales = Sale::query()
            ->whereIn('statut', Sale::REPORTABLE_STATUSES)
            ->withCount('items')
            ->latest('date_vente')
            ->latest('id')
            ->limit(6)
            ->get();
        $lowStockProducts = Product::query()
            ->with(['unit', 'category'])
            ->where('statut', true)
            ->whereColumn('quantite_stock', '<=', 'seuil_alerte')
            ->orderBy('quantite_stock')
            ->limit(6)
            ->get();

        return view('dashboard', compact(
            'salesToday',
            'salesCountToday',
            'productsCount',
            'customersCount',
            'lowStockCount',
            'recentSales',
            'lowStockProducts',
        ));
    }

    public function statistiques(Request $request): View
    {
        $filters = $request->validate([
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);
        $dateStart = isset($filters['date_debut'])
            ? Carbon::parse($filters['date_debut'])->startOfDay()
            : now()->startOfMonth()->startOfDay();
        $dateEnd = isset($filters['date_fin'])
            ? Carbon::parse($filters['date_fin'])->endOfDay()
            : now()->endOfDay();
        $range = [$dateStart, $dateEnd];

        $salesQuery = Sale::query()->whereIn('statut', Sale::REPORTABLE_STATUSES)->whereBetween('date_vente', $range);
        $salesCount = (clone $salesQuery)->count();
        $salesTotal = (float) (clone $salesQuery)->sum('total');
        $averageSale = $salesCount > 0 ? $salesTotal / $salesCount : 0;
        $returnTotal = (float) SaleReturn::query()->whereBetween('date_retour', $range)->sum('total');
        $salesTotal -= $returnTotal;
        $purchasesTotal = (float) Purchase::query()->whereBetween('date_achat', $range)->sum('total');
        $purchasesPaid = (float) Purchase::query()->whereBetween('date_achat', $range)->sum('montant_paye');
        $purchasesDue = max(0, $purchasesTotal - $purchasesPaid);
        $grossMargin = (float) SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereIn('sales.statut', Sale::REPORTABLE_STATUSES)
            ->whereBetween('sales.date_vente', $range)
            ->where('sale_items.prix_achat_unitaire', '>', 0)
            ->selectRaw('COALESCE(SUM((sale_items.prix_unitaire - sale_items.prix_achat_unitaire) * sale_items.quantite), 0) as marge')
            ->value('marge');
        $returnedMargin = (float) SaleReturnItem::query()
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->join('sale_items', 'sale_items.id', '=', 'sale_return_items.sale_item_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sale_returns.date_retour', $range)
            ->where('sale_items.prix_achat_unitaire', '>', 0)
            ->selectRaw('COALESCE(SUM((sale_return_items.prix_unitaire - sale_items.prix_achat_unitaire) * sale_return_items.quantite), 0) as marge')
            ->value('marge');
        $marginTotal = round($grossMargin - $returnedMargin, 2);
        $untrackedCostLineCount = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereIn('sales.statut', Sale::REPORTABLE_STATUSES)
            ->whereBetween('sales.date_vente', $range)
            ->where('sale_items.prix_achat_unitaire', 0)
            ->count();

        $dailySales = (clone $salesQuery)
            ->selectRaw('DATE(date_vente) as jour, COUNT(*) as nombre_ventes, SUM(total) as chiffre_affaires')
            ->groupByRaw('DATE(date_vente)')
            ->orderBy('jour')
            ->get()
            ->keyBy('jour');
        $dailyPurchases = Purchase::query()
            ->whereBetween('date_achat', $range)
            ->selectRaw('DATE(date_achat) as jour, SUM(total) as total_achats')
            ->groupByRaw('DATE(date_achat)')
            ->orderBy('jour')
            ->get()
            ->keyBy('jour');
        $dailyReport = $dailySales->keys()
            ->merge($dailyPurchases->keys())
            ->unique()
            ->sort()
            ->map(fn (string $day): array => [
                'jour' => $day,
                'nombre_ventes' => (int) ($dailySales->get($day)->nombre_ventes ?? 0),
                'chiffre_affaires' => (float) ($dailySales->get($day)->chiffre_affaires ?? 0),
                'total_achats' => (float) ($dailyPurchases->get($day)->total_achats ?? 0),
            ])
            ->values();

        $topProducts = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereIn('sales.statut', Sale::REPORTABLE_STATUSES)
            ->whereBetween('sales.date_vente', $range)
            ->select('sale_items.nom_produit', 'sale_items.reference_produit')
            ->selectRaw('SUM(sale_items.quantite) as quantite_vendue, SUM(sale_items.total_ligne) as chiffre_affaires')
            ->groupBy('sale_items.nom_produit', 'sale_items.reference_produit')
            ->orderByDesc('chiffre_affaires')
            ->limit(5)
            ->get();
        $paymentTotals = SalePayment::query()
            ->whereBetween('date_paiement', $range)
            ->select('moyen_paiement')
            ->selectRaw('COUNT(*) as nombre_reglements, SUM(montant) as chiffre_affaires')
            ->groupBy('moyen_paiement')
            ->orderByDesc('chiffre_affaires')
            ->get();
        $collectedTotal = (float) SalePayment::query()
            ->whereBetween('date_paiement', $range)
            ->sum('montant') - $returnTotal;
        $paymentMethods = [
            'especes' => 'Espèces',
            'mobile_money' => 'Mobile Money',
            'carte' => 'Carte bancaire',
            'autre' => 'Autre',
        ];

        return view('statistiques', compact(
            'dateStart',
            'dateEnd',
            'salesCount',
            'salesTotal',
            'collectedTotal',
            'averageSale',
            'purchasesTotal',
            'purchasesPaid',
            'purchasesDue',
            'marginTotal',
            'untrackedCostLineCount',
            'dailyReport',
            'topProducts',
            'paymentTotals',
            'paymentMethods',
        ));
    }
}
