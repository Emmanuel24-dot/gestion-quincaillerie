<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'facturation' => ['nullable', Rule::in(['emise', 'a_emettre'])],
        ]);

        $sales = Sale::query()
            ->where('statut', Sale::STATUS_PAID)
            ->whereColumn('montant_paye', '>=', 'total')
            ->with('invoice')
            ->withCount('items')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('numero', 'like', "%{$search}%")
                        ->orWhere('client_nom', 'like', "%{$search}%")
                        ->orWhereHas('invoice', fn ($invoiceQuery) => $invoiceQuery->where('numero', 'like', "%{$search}%"));
                });
            })
            ->when($filters['date_debut'] ?? null, fn ($query, string $date) => $query->whereDate('date_vente', '>=', $date))
            ->when($filters['date_fin'] ?? null, fn ($query, string $date) => $query->whereDate('date_vente', '<=', $date))
            ->when(($filters['facturation'] ?? null) === 'emise', fn ($query) => $query->whereHas('invoice'))
            ->when(($filters['facturation'] ?? null) === 'a_emettre', fn ($query) => $query->whereDoesntHave('invoice'))
            ->latest('date_vente')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('invoices.index', compact('sales'));
    }

    public function store(Sale $sale): RedirectResponse
    {
        if ($sale->statut !== Sale::STATUS_PAID || (float) $sale->montant_paye < (float) $sale->total) {
            return back()->with('error', 'Seule une vente payée peut être facturée.');
        }

        $invoice = DB::transaction(function () use ($sale): Invoice {
            $lockedSale = Sale::query()->lockForUpdate()->findOrFail($sale->id);

            if ($lockedSale->statut !== Sale::STATUS_PAID || (float) $lockedSale->montant_paye < (float) $lockedSale->total) {
                abort(422, 'Seule une vente payée peut être facturée.');
            }

            $existingInvoice = $lockedSale->invoice()->first();
            if ($existingInvoice) {
                return $existingInvoice;
            }

            return $lockedSale->invoice()->create([
                'numero' => sprintf('FAC-%s-%06d', now()->format('Ymd'), $lockedSale->id),
                'date_emission' => now(),
                'statut' => 'emise',
            ]);
        }, 3);

        return redirect()
            ->route('factures.show', $invoice)
            ->with('success', 'La facture est prête.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load('sale.items', 'sale.payments');

        $paymentMethods = [
            'especes' => 'Espèces',
            'mobile_money' => 'Mobile Money',
            'carte' => 'Carte bancaire',
            'autre' => 'Autre',
        ];

        return view('invoices.show', compact('invoice', 'paymentMethods'));
    }
}
