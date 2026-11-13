<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalePaymentController extends Controller
{
    public function store(Request $request, Sale $sale): RedirectResponse
    {
        $data = $request->validate([
            'montant' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'moyen_paiement' => ['required', 'in:especes,mobile_money,carte,autre'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($sale, $data): void {
            $lockedSale = Sale::query()->lockForUpdate()->findOrFail($sale->id);
            $remaining = round((float) $lockedSale->total - (float) $lockedSale->montant_paye, 2);
            $amount = round((float) $data['montant'], 2);

            if ($amount > $remaining) {
                throw ValidationException::withMessages(['montant' => 'Le paiement dépasse le solde restant.']);
            }

            $lockedSale->payments()->create([
                'montant' => $amount,
                'moyen_paiement' => $data['moyen_paiement'],
                'date_paiement' => now(),
                'notes' => $data['notes'] ?? null,
            ]);
            $newPaidAmount = round((float) $lockedSale->montant_paye + $amount, 2);
            $lockedSale->update([
                'montant_paye' => $newPaidAmount,
                'statut' => $newPaidAmount >= (float) $lockedSale->total
                    ? Sale::STATUS_PAID
                    : Sale::STATUS_PARTIALLY_PAID,
            ]);
        }, 3);

        return back()->with('success', 'Le paiement du client a été enregistré.');
    }
}
