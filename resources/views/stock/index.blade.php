@extends('layouts.app')

@section('title', 'Stock')

@section('content')
<div class="container-fluid">
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">État du stock</h1>
            <p class="text-muted mb-0">Surveillez les quantités disponibles et les produits à réapprovisionner.</p>
        </div>
        @if(auth()->user()?->role === 'admin')<a href="{{ route('mouvements-stock.create') }}" class="btn btn-primary"><i class="bi bi-arrow-left-right me-1"></i> Nouveau mouvement</a>@endif
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4"><a href="{{ route('stock.index') }}" class="metric-link"><article class="card dashboard-metric metric-orange h-100 {{ request('niveau') === null || request('niveau') === '' ? 'is-selected' : '' }}"><div class="card-body"><div class="dashboard-metric-top"><span>Produits suivis</span><i class="bi bi-boxes" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ $productCount }}</strong><span class="dashboard-metric-note">Voir tout le stock</span></div></article></a></div>
        <div class="col-12 col-md-4"><a href="{{ route('stock.index', ['niveau' => 'faible']) }}" class="metric-link"><article class="card dashboard-metric metric-amber h-100 {{ request('niveau') === 'faible' ? 'is-selected' : '' }}"><div class="card-body"><div class="dashboard-metric-top"><span>Stock faible</span><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ $lowStockCount }}</strong><span class="dashboard-metric-note">Sous le seuil d’alerte</span></div></article></a></div>
        <div class="col-12 col-md-4"><a href="{{ route('stock.index', ['niveau' => 'rupture']) }}" class="metric-link"><article class="card dashboard-metric metric-red h-100 {{ request('niveau') === 'rupture' ? 'is-selected' : '' }}"><div class="card-body"><div class="dashboard-metric-top"><span>En rupture</span><i class="bi bi-x-octagon" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ $outOfStockCount }}</strong><span class="dashboard-metric-note">Plus aucune unité disponible</span></div></article></a></div>
    </div>

    <div class="card border-0 shadow-sm mb-4"><div class="card-body">
        <form action="{{ route('stock.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-lg-5"><label for="search" class="form-label">Rechercher</label><div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Nom ou référence"></div></div>
            <div class="col-12 col-sm-6 col-lg-3"><label for="category_id" class="form-label">Catégorie</label><select name="category_id" id="category_id" class="form-select"><option value="">Toutes les catégories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->nom }}</option>@endforeach</select></div>
            <div class="col-12 col-sm-6 col-lg-2"><label for="niveau" class="form-label">Niveau</label><select name="niveau" id="niveau" class="form-select"><option value="">Tous les niveaux</option><option value="normal" @selected(request('niveau') === 'normal')>Normal</option><option value="faible" @selected(request('niveau') === 'faible')>Stock faible</option><option value="rupture" @selected(request('niveau') === 'rupture')>Rupture</option></select></div>
            <div class="col-6 col-lg-1 d-grid"><button class="btn btn-dark" type="submit">Filtrer</button></div>
            <div class="col-6 col-lg-1 d-grid"><a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">Effacer</a></div>
        </form>
    </div></div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3"><h2 class="h6 mb-0">Quantités par produit</h2><span class="text-muted small">{{ $products->total() }} résultat(s)</span></div>
        <div class="card-body p-0">
            @if ($products->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-4">Produit</th><th>Catégorie</th><th>Quantité disponible</th><th>Seuil d’alerte</th><th>État</th><th class="text-end pe-4">@if (auth()->user()?->role === 'admin')Inventaire (quantité comptée)@endif</th></tr></thead>
                        <tbody>
                            @foreach ($products as $product)
                                @php($outOfStock = (float) $product->quantite_stock <= 0)
                                @php($lowStock = !$outOfStock && (float) $product->quantite_stock <= (float) $product->seuil_alerte)
                                <tr>
                                    <td class="ps-4"><strong>{{ $product->nom }}</strong><small class="d-block text-muted">{{ $product->reference }}</small></td>
                                    <td>{{ $product->category->nom }}</td>
                                    @php($stockRatio = (float) $product->seuil_alerte > 0 ? min(100, round((float) $product->quantite_stock / ((float) $product->seuil_alerte * 2) * 100)) : 100)
                                    <td class="fw-semibold text-nowrap">{{ rtrim(rtrim(number_format((float) $product->quantite_stock, 3, ',', ' '), '0'), ',') }} {{ $product->unit->symbole }}<div class="stock-meter" aria-hidden="true"><span class="{{ $outOfStock ? 'is-out' : ($lowStock ? 'is-low' : '') }}" style="width: {{ max(0, $stockRatio) }}%"></span></div></td>
                                    <td class="text-nowrap">{{ rtrim(rtrim(number_format((float) $product->seuil_alerte, 3, ',', ' '), '0'), ',') }} {{ $product->unit->symbole }}</td>
                                    <td><span class="badge {{ $outOfStock ? 'text-bg-danger' : ($lowStock ? 'text-bg-warning' : 'text-bg-success') }}">{{ $outOfStock ? 'Rupture' : ($lowStock ? 'Stock faible' : 'Normal') }}</span></td>
                                    <td class="text-end pe-4 text-nowrap">@if(auth()->user()?->role === 'admin')<form action="{{ route('produits.inventaire', $product) }}" method="POST" class="inventory-form">@csrf<label class="visually-hidden" for="count-{{ $product->id }}">Quantité comptée pour {{ $product->nom }}</label><div class="input-group input-group-sm"><input id="count-{{ $product->id }}" name="stock_compte" type="number" min="0" step="0.001" value="{{ (float) $product->quantite_stock }}" class="form-control" required><span class="input-group-text">{{ $product->unit->symbole }}</span><button class="btn btn-outline-primary" type="submit" title="Enregistrer le comptage" aria-label="Enregistrer le comptage de {{ $product->nom }}"><i class="bi bi-check-lg" aria-hidden="true"></i></button></div></form><a href="{{ route('mouvements-stock.create', ['product_id' => $product->id]) }}" class="btn btn-sm btn-link px-1" title="Nouveau mouvement" aria-label="Nouveau mouvement pour {{ $product->nom }}"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></a>@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3">{{ $products->links() }}</div>
            @else
                <div class="text-center py-5 px-3"><i class="bi bi-boxes display-6 text-muted"></i><h3 class="h5 mt-3">Aucun produit correspondant</h3><p class="text-muted mb-0">Créez un produit ou modifiez les filtres.</p></div>
            @endif
        </div>
    </div>
</div>
@endsection

