<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchasePaymentController extends Controller
{
    public function store(Request $request, Purchase $purchase): RedirectResponse
    {
        $data = $request->validate([
            'montant' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'moyen_paiement' => ['required', 'in:especes,mobile_money,carte,autre'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($purchase, $data): void {
            $lockedPurchase = Purchase::query()->lockForUpdate()->findOrFail($purchase->id);
            $remaining = round((float) $lockedPurchase->total - (float) $lockedPurchase->montant_paye, 2);
            $amount = round((float) $data['montant'], 2);

            if ($amount > $remaining) {
                throw ValidationException::withMessages(['montant' => 'Le paiement dépasse le solde fournisseur.']);
            }

            $lockedPurchase->payments()->create([
                'montant' => $amount,
                'moyen_paiement' => $data['moyen_paiement'],
                'date_paiement' => now(),
                'notes' => $data['notes'] ?? null,
            ]);
            $lockedPurchase->update(['montant_paye' => round((float) $lockedPurchase->montant_paye + $amount, 2)]);
        }, 3);

        return back()->with('success', 'Le règlement fournisseur a été enregistré.');
    }
}
