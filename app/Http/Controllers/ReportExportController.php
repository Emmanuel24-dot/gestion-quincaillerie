<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);
        $start = Carbon::parse($filters['date_debut'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $end = Carbon::parse($filters['date_fin'] ?? now()->toDateString())->endOfDay();

        return response()->streamDownload(function () use ($start, $end): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Date', 'Type', 'État', 'Référence', 'Partenaire', 'Article', 'Quantité', 'Total FCFA', 'Payé FCFA', 'Solde FCFA'], ';', '"', '\\');

            Sale::query()->whereIn('statut', Sale::REPORTABLE_STATUSES)->whereBetween('date_vente', [$start, $end])->with('items')->orderBy('id')->chunkById(200, function ($sales) use ($output): void {
                foreach ($sales as $sale) {
                    fputcsv($output, [$sale->date_vente->format('Y-m-d H:i'), 'Vente', $sale->statut_label, $sale->numero, $sale->client_nom ?? 'Client de passage', '', '', $sale->total, $sale->montant_paye, max(0, (float) $sale->total - (float) $sale->montant_paye)], ';', '"', '\\');
                }
            });

            Purchase::query()->whereBetween('date_achat', [$start, $end])->with('supplier')->orderBy('id')->chunkById(200, function ($purchases) use ($output): void {
                foreach ($purchases as $purchase) {
                    fputcsv($output, [$purchase->date_achat->format('Y-m-d H:i'), 'Achat', 'Réceptionné', $purchase->numero, $purchase->supplier->nom, '', '', $purchase->total, $purchase->montant_paye, max(0, (float) $purchase->total - (float) $purchase->montant_paye)], ';', '"', '\\');
                }
            });

            SaleReturn::query()->whereBetween('date_retour', [$start, $end])->with('sale')->orderBy('id')->chunkById(200, function ($returns) use ($output): void {
                foreach ($returns as $return) {
                    fputcsv($output, [$return->date_retour->format('Y-m-d H:i'), 'Remboursement', 'Remboursé', $return->numero, $return->sale->client_nom ?? 'Client de passage', '', '', -1 * (float) $return->total, -1 * (float) $return->total, 0], ';', '"', '\\');
                }
            });

            fclose($output);
        }, 'rapport-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
