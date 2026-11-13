<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'statut' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $suppliers = Supplier::query()
            ->withCount('purchases')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nom', 'like', "%{$search}%")
                        ->orWhere('contact_nom', 'like', "%{$search}%")
                        ->orWhere('telephone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('statut', $filters), fn ($query) => $query->where('statut', (bool) $filters['statut']))
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['purchases' => fn ($query) => $query->with('payments')->latest('date_achat')]);
        $totalPurchases = (float) $supplier->purchases->sum('total');
        $totalPaid = (float) $supplier->purchases->sum('montant_paye');
        $balance = max(0, round($totalPurchases - $totalPaid, 2));

        return view('suppliers.show', compact('supplier', 'totalPurchases', 'totalPaid', 'balance'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['statut'] = $request->boolean('statut');
        Supplier::create($data);

        return redirect()->route('fournisseurs.index')->with('success', 'Le fournisseur a été créé.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['statut'] = $request->boolean('statut');
        $supplier->update($data);

        return redirect()->route('fournisseurs.index')->with('success', 'Le fournisseur a été modifié.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchases()->exists()) {
            return redirect()->route('fournisseurs.index')->with('error', 'Ce fournisseur est lié à des achats. Désactivez sa fiche pour conserver l’historique.');
        }

        $supplier->delete();

        return redirect()->route('fournisseurs.index')->with('success', 'Le fournisseur a été supprimé.');
    }

    private function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:150'],
            'contact_nom' => ['nullable', 'string', 'max:150'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'adresse' => ['nullable', 'string', 'max:2000'],
            'statut' => ['sometimes', 'boolean'],
        ];
    }
}
