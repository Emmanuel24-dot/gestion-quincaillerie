<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'statut' => ['nullable', 'in:0,1'],
        ]);

        $units = Unit::query()
            ->withCount('products')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nom', 'like', "%{$search}%")
                        ->orWhere('symbole', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('statut', $filters), fn ($query) => $query->where('statut', (bool) $filters['statut']))
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        return view('units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('units.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['statut'] = $request->boolean('statut');
        Unit::create($data);

        return redirect()->route('unites.index')->with('success', 'L’unité a été créée.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit): View
    {
        return view('units.edit', compact('unit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $data = $request->validate($this->rules($unit));
        $data['statut'] = $request->boolean('statut');
        $unit->update($data);

        return redirect()->route('unites.index')->with('success', 'L’unité a été modifiée.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->products()->exists()) {
            return redirect()->route('unites.index')->with('error', 'Cette unité ne peut pas être supprimée car elle est utilisée par des produits.');
        }

        $unit->delete();

        return redirect()->route('unites.index')->with('success', 'L’unité a été supprimée.');
    }

    private function rules(?Unit $unit = null): array
    {
        return [
            'nom' => ['required', 'string', 'max:100', Rule::unique('units', 'nom')->ignore($unit)],
            'symbole' => ['required', 'string', 'max:30', Rule::unique('units', 'symbole')->ignore($unit)],
            'statut' => ['sometimes', 'boolean'],
        ];
    }
}
