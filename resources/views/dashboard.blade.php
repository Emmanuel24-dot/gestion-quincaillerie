@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="container-fluid">
    <section class="dashboard-welcome mb-4">
        <div>
            <h1 class="h2 mb-2">Tableau de bord</h1>
        </div>
        <a href="{{ route('ventes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nouvelle vente</a>
    </section>

    <div class="row g-3 g-xl-4 dashboard-metrics">
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="card dashboard-metric metric-orange h-100"><div class="card-body">
                <div class="dashboard-metric-top"><span>Ventes du jour</span><i class="bi bi-currency-exchange" aria-hidden="true"></i></div>
                <strong class="dashboard-metric-value">{{ number_format((float) $salesToday, 0, ',', ' ') }} <small>FCFA</small></strong>
                <span class="dashboard-metric-note">{{ $salesCountToday }} vente(s) enregistrée(s) aujourd’hui</span>
            </div></article>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="card dashboard-metric metric-blue h-100"><div class="card-body">
                <div class="dashboard-metric-top"><span>Produits</span><i class="bi bi-box-seam" aria-hidden="true"></i></div>
                <strong class="dashboard-metric-value">{{ number_format($productsCount, 0, ',', ' ') }}</strong>
            </div></article>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="card dashboard-metric metric-green h-100"><div class="card-body">
                <div class="dashboard-metric-top"><span>Clients</span><i class="bi bi-people" aria-hidden="true"></i></div>
                <strong class="dashboard-metric-value">{{ number_format($customersCount, 0, ',', ' ') }}</strong>
            </div></article>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="card dashboard-metric metric-red h-100"><div class="card-body">
                <div class="dashboard-metric-top"><span>Stock faible</span><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></div>
                <strong class="dashboard-metric-value">{{ number_format($lowStockCount, 0, ',', ' ') }}</strong>
            </div></article>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-xl-7"><section class="card dashboard-data-card h-100"><div class="card-header d-flex justify-content-between align-items-center py-3"><h2 class="h6 mb-0">Ventes récentes</h2><a href="{{ route('ventes.index') }}" class="small text-decoration-none">Historique <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div><div class="card-body p-0">
            @if ($recentSales->isNotEmpty())
                <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th class="ps-4">Vente</th><th>Client</th><th>Date</th><th>État</th><th class="text-end pe-4">Montant</th></tr></thead><tbody>@foreach ($recentSales as $sale)<tr><td class="ps-4"><a href="{{ route('ventes.show', $sale) }}" class="fw-semibold text-decoration-none">{{ $sale->numero }}</a><small class="d-block text-muted">{{ $sale->items_count }} article(s)</small></td><td>{{ $sale->client_nom ?: 'Client de passage' }}</td><td class="text-nowrap">{{ $sale->date_vente->format('d/m/Y H:i') }}</td><td><span class="badge {{ $sale->statut_badge_class }}">{{ $sale->statut_label }}</span></td><td class="text-end pe-4 fw-semibold text-nowrap">{{ number_format((float) $sale->total, 0, ',', ' ') }} FCFA</td></tr>@endforeach</tbody></table></div>
            @else
                <div class="text-center py-5 px-3"><i class="bi bi-receipt display-6 text-muted" aria-hidden="true"></i><h3 class="h6 mt-3">Aucune vente enregistrée</h3><a href="{{ route('ventes.create') }}" class="btn btn-sm btn-primary">Créer une vente</a></div>
            @endif
        </div></section></div>
        <div class="col-12 col-xl-5"><section class="card dashboard-data-card h-100"><div class="card-header d-flex justify-content-between align-items-center py-3"><h2 class="h6 mb-0">Stock à réapprovisionner</h2><a href="{{ route('stock.index', ['niveau' => 'faible']) }}" class="small text-decoration-none">Voir le stock <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div><div class="card-body p-0">
            @if ($lowStockProducts->isNotEmpty())
                <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th class="ps-4">Produit</th><th>Disponible</th><th>Seuil</th><th class="text-end pe-4">Action</th></tr></thead><tbody>@foreach ($lowStockProducts as $product)<tr><td class="ps-4"><strong>{{ $product->nom }}</strong><small class="d-block text-muted">{{ $product->reference }}</small></td><td class="text-nowrap text-danger fw-semibold">{{ number_format((float) $product->quantite_stock, 3, ',', ' ') }} {{ $product->unit->symbole }}@if ((float) $product->quantite_stock <= 0)<span class="badge text-bg-danger ms-1">Rupture</span>@endif</td><td class="text-nowrap">{{ number_format((float) $product->seuil_alerte, 3, ',', ' ') }} {{ $product->unit->symbole }}</td><td class="text-end pe-4">@if (auth()->user()?->role === 'admin')<a href="{{ route('mouvements-stock.create', ['product_id' => $product->id]) }}" class="btn btn-sm btn-outline-primary" aria-label="Réapprovisionner {{ $product->nom }}"><i class="bi bi-plus-lg" aria-hidden="true"></i>Stock</a>@else<a href="{{ route('stock.index', ['search' => $product->reference]) }}" class="btn btn-sm btn-outline-secondary" aria-label="Voir le stock de {{ $product->nom }}"><i class="bi bi-eye" aria-hidden="true"></i></a>@endif</td></tr>@endforeach</tbody></table></div>
            @else
                <div class="text-center py-5 px-3"><i class="bi bi-check-circle display-6 text-muted" aria-hidden="true"></i><h3 class="h6 mt-3">Stock à niveau</h3><p class="text-muted mb-0">Aucun produit actif ne passe sous son seuil.</p></div>
            @endif
        </div></section></div>
    </div>

    @if (auth()->user()?->role === 'admin')
    <section class="dashboard-shortcuts mt-4">
        <div class="dashboard-section-heading">
            <h2 class="h5 mb-0">Actions rapides</h2>
        </div>
        <div class="row g-3">
            <div class="col-12 col-md-4"><a href="{{ route('achats.create') }}" class="dashboard-shortcut"><span class="shortcut-icon icon-orange"><i class="bi bi-bag-plus" aria-hidden="true"></i></span><span><strong>Enregistrer un achat</strong></span><i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
            <div class="col-12 col-md-4"><a href="{{ route('clients.create') }}" class="dashboard-shortcut"><span class="shortcut-icon icon-green"><i class="bi bi-person-plus" aria-hidden="true"></i></span><span><strong>Ajouter un client</strong></span><i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
            <div class="col-12 col-md-4"><a href="{{ route('statistiques') }}" class="dashboard-shortcut"><span class="shortcut-icon icon-blue"><i class="bi bi-bar-chart-line" aria-hidden="true"></i></span><span><strong>Voir les rapports</strong></span><i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
        </div>
    </section>
    @endif
</div>
@endsection
