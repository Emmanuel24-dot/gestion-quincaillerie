@extends('layouts.app')

@section('title', 'Mouvements de stock')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">Mouvements de stock</h1><p class="text-muted mb-0">Historique des entrées, sorties et ajustements de stock.</p></div>
        <a href="{{ route('mouvements-stock.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nouveau mouvement</a>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card border-0 shadow-sm mb-4"><div class="card-body">
        <form action="{{ route('mouvements-stock.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-7"><label for="search" class="form-label">Rechercher un produit</label><input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Nom ou référence"></div>
            <div class="col-8 col-md-3"><label for="type" class="form-label">Type de mouvement</label><select name="type" id="type" class="form-select"><option value="">Tous</option>@foreach ($types as $value => $label)<option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-4 col-md-2 d-grid"><button type="submit" class="btn btn-dark">Filtrer</button></div>
        </form>
    </div></div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3"><h2 class="h6 mb-0">Journal</h2><span class="text-muted small">{{ $movements->total() }} mouvement(s)</span></div>
        <div class="card-body p-0">
            @if ($movements->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-4">Date</th><th>Produit</th><th>Type</th><th>Motif</th><th>Quantité</th><th>Stock avant</th><th>Stock après</th></tr></thead>
                        <tbody>
                            @foreach ($movements as $movement)
                                <tr>
                                    <td class="ps-4 text-nowrap">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                    <td><strong>{{ $movement->product->nom }}</strong><small class="d-block text-muted">{{ $movement->product->reference }}</small></td>
                                    <td><span class="badge text-bg-light border">{{ \App\Models\StockMovement::TYPE_LABELS[$movement->type] ?? $movement->type }}</span></td>
                                    <td>{{ $movement->motif }}@if ($movement->notes)<small class="d-block text-muted">{{ $movement->notes }}</small>@endif</td>
                                    <td class="text-nowrap">{{ number_format((float) $movement->quantite, 3, ',', ' ') }} {{ $movement->product->unit->symbole }}</td>
                                    <td class="text-nowrap">{{ number_format((float) $movement->stock_avant, 3, ',', ' ') }} {{ $movement->product->unit->symbole }}</td>
                                    <td class="text-nowrap fw-semibold">{{ number_format((float) $movement->stock_apres, 3, ',', ' ') }} {{ $movement->product->unit->symbole }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3">{{ $movements->links() }}</div>
            @else
                <div class="text-center py-5 px-3"><i class="bi bi-arrow-left-right display-6 text-muted"></i><h3 class="h5 mt-3">Aucun mouvement enregistré</h3><p class="text-muted">Les entrées, sorties et ajustements apparaîtront ici.</p><a href="{{ route('mouvements-stock.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Enregistrer un mouvement</a></div>
            @endif
        </div>
    </div>
</div>
@endsection

