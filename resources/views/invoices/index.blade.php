@extends('layouts.app')

@section('title', 'Facturation')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">Facturation</h1><p class="text-muted mb-0">Émettez et consultez les factures des ventes payées.</p></div>
        <a href="{{ route('ventes.index') }}" class="btn btn-outline-primary"><i class="bi bi-clock-history me-1"></i>Historique des ventes</a>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card border-0 shadow-sm mb-4"><div class="card-body">
        <form action="{{ route('factures.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-lg-5"><label for="search" class="form-label">Rechercher</label><input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="N° de facture, vente ou client"></div>
            <div class="col-6 col-lg-2"><label for="date_debut" class="form-label">Du</label><input type="date" name="date_debut" id="date_debut" value="{{ request('date_debut') }}" class="form-control"></div>
            <div class="col-6 col-lg-2"><label for="date_fin" class="form-label">Au</label><input type="date" name="date_fin" id="date_fin" value="{{ request('date_fin') }}" class="form-control"></div>
            <div class="col-8 col-lg-2"><label for="facturation" class="form-label">État</label><select name="facturation" id="facturation" class="form-select"><option value="">Toutes les ventes payées</option><option value="a_emettre" @selected(request('facturation') === 'a_emettre')>À facturer</option><option value="emise" @selected(request('facturation') === 'emise')>Facture émise</option></select></div>
            <div class="col-4 col-lg-1 d-grid"><button type="submit" class="btn btn-dark">Filtrer</button></div>
        </form>
    </div></div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3"><h2 class="h6 mb-0">Ventes payées</h2><span class="text-muted small">{{ $sales->total() }} vente(s)</span></div>
        <div class="card-body p-0">
            @if ($sales->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-4">Vente</th><th>Date</th><th>Client</th><th>Total</th><th>Facture</th><th class="text-end pe-4">Action</th></tr></thead>
                        <tbody>
                            @foreach ($sales as $sale)
                                <tr>
                                    <td class="ps-4"><a href="{{ route('ventes.show', $sale) }}" class="text-decoration-none fw-semibold">{{ $sale->numero }}</a></td>
                                    <td class="text-nowrap">{{ $sale->date_vente->format('d/m/Y H:i') }}</td>
                                    <td>{{ $sale->client_nom ?: 'Client de passage' }}</td>
                                    <td class="text-nowrap fw-semibold">{{ number_format((float) $sale->total, 0, ',', ' ') }} FCFA</td>
                                    <td>@if ($sale->invoice)<span class="badge text-bg-success">{{ $sale->invoice->numero }}</span>@else<span class="badge text-bg-warning">À facturer</span>@endif</td>
                                    <td class="text-end pe-4">
                                        @if ($sale->invoice)
                                            <a href="{{ route('factures.show', $sale->invoice) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-receipt me-1"></i>Voir</a>
                                        @else
                                            <form action="{{ route('factures.store', $sale) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-file-earmark-plus me-1"></i>Émettre</button></form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3">{{ $sales->links() }}</div>
            @else
                <div class="text-center py-5 px-3"><i class="bi bi-receipt display-6 text-muted"></i><h3 class="h5 mt-3">Aucune vente payée à facturer</h3><p class="text-muted">Les ventes payées apparaîtront ici.</p></div>
            @endif
        </div>
    </div>
</div>
@endsection

