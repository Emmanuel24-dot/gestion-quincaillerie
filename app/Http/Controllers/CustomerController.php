<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'statut' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $customers = Customer::query()
            ->withCount('sales')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nom', 'like', "%{$search}%")
                        ->orWhere('telephone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('statut', $filters), fn ($query) => $query->where('statut', (bool) $filters['statut']))
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function show(Customer $customer): View
    {
        $customer->load(['sales' => fn ($query) => $query->with('payments', 'returns')->latest('date_vente')]);
        $totalSales = (float) $customer->sales->sum('total');
        $totalPaid = (float) $customer->sales->sum('montant_paye');
        $totalReturned = (float) $customer->sales->sum(fn ($sale) => $sale->returns->sum('total'));
        $balance = round((float) $customer->sales->sum(fn ($sale) => max(0, (float) $sale->total - (float) $sale->montant_paye)), 2);

        return view('customers.show', compact('customer', 'totalSales', 'totalPaid', 'totalReturned', 'balance'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['statut'] = $request->boolean('statut');
        Customer::create($data);

        return redirect()->route('clients.index')->with('success', 'Le client a été créé.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['statut'] = $request->boolean('statut');
        $customer->update($data);

        return redirect()->route('clients.index')->with('success', 'Le client a été modifié.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->sales()->exists()) {
            return redirect()->route('clients.index')->with('error', 'Ce client est lié à des ventes. Désactivez sa fiche pour la conserver dans l’historique.');
        }

        $customer->delete();

        return redirect()->route('clients.index')->with('success', 'Le client a été supprimé.');
    }

    private function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:150'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'adresse' => ['nullable', 'string', 'max:2000'],
            'statut' => ['sometimes', 'boolean'],
        ];
    }
}
