@extends('layouts.app')

@section('title', 'Historique des ventes')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">Historique des ventes</h1><p class="text-muted mb-0">Retrouvez les reçus et les montants encaissés.</p></div>
        <a href="{{ route('ventes.create') }}" class="btn btn-primary"><i class="bi bi-cart-plus me-1"></i>Nouvelle vente</a>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card border-0 shadow-sm mb-4"><div class="card-body">
        <form action="{{ route('ventes.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-lg-4"><label for="search" class="form-label">Rechercher</label><input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="N° de vente ou client"></div>
            <div class="col-6 col-lg-3"><label for="date_debut" class="form-label">Du</label><input type="date" name="date_debut" id="date_debut" value="{{ request('date_debut') }}" class="form-control"></div>
            <div class="col-6 col-lg-3"><label for="date_fin" class="form-label">Au</label><input type="date" name="date_fin" id="date_fin" value="{{ request('date_fin') }}" class="form-control"></div>
            <div class="col-6 col-lg-1 d-grid"><button class="btn btn-dark" type="submit">Filtrer</button></div>
            <div class="col-6 col-lg-1 d-grid"><a href="{{ route('ventes.index') }}" class="btn btn-outline-secondary">Effacer</a></div>
        </form>
    </div></div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3"><h2 class="h6 mb-0">Ventes enregistrées</h2><span class="text-muted small">{{ $sales->total() }} vente(s)</span></div>
        <div class="card-body p-0">
            @if ($sales->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-4">N° de vente</th><th>Date</th><th>Client</th><th>Articles</th><th>Paiement initial</th><th>État</th><th>Total</th><th>Montant réglé</th><th class="text-end pe-4">Reçu</th></tr></thead>
                        <tbody>
                            @foreach ($sales as $sale)
                                <tr>
                                    <td class="ps-4"><a href="{{ route('ventes.show', $sale) }}" class="fw-semibold text-decoration-none">{{ $sale->numero }}</a></td>
                                    <td class="text-nowrap">{{ $sale->date_vente->format('d/m/Y H:i') }}</td>
                                    <td>{{ $sale->client_nom ?: 'Client de passage' }}</td>
                                    <td>{{ $sale->items_count }}</td>
                                    <td>{{ $paymentMethods[$sale->moyen_paiement] ?? $sale->moyen_paiement }}</td>
                                    <td><span class="badge {{ $sale->statut_badge_class }}">{{ $sale->statut_label }}</span></td>
                                    <td class="text-nowrap fw-semibold">{{ number_format((float) $sale->total, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-nowrap">{{ number_format((float) $sale->montant_paye, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-end pe-4"><a href="{{ route('ventes.show', $sale) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-receipt me-1"></i>Reçu</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3">{{ $sales->links() }}</div>
            @else
                <div class="text-center py-5 px-3"><i class="bi bi-receipt display-6 text-muted"></i><h3 class="h5 mt-3">Aucune vente enregistrée</h3><p class="text-muted">Les ventes effectuées apparaîtront ici.</p><a href="{{ route('ventes.create') }}" class="btn btn-primary"><i class="bi bi-cart-plus me-1"></i>Commencer une vente</a></div>
            @endif
        </div>
    </div>
</div>
@endsection
