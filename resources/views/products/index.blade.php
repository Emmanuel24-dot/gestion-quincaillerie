@extends('layouts.app')

@section('title', 'Produits')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Produits</h1>
            <p class="text-muted mb-0">Gérez votre catalogue et surveillez les niveaux de stock.</p>
        </div>
        @if (auth()->user()?->role === 'admin')<a href="{{ route('produits.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nouveau produit
        </a>@endif
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('produits.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-lg-5">
                    <label for="search" class="form-label">Rechercher</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Référence ou nom du produit">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="category_id" class="form-label">Catégorie</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">Toutes les catégories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select">
                        <option value="">Tous</option>
                        <option value="1" @selected(request('statut') === '1')>Actifs</option>
                        <option value="0" @selected(request('statut') === '0')>Inactifs</option>
                    </select>
                </div>
                <div class="col-6 col-lg-1 d-grid">
                    <button class="btn btn-dark" type="submit" aria-label="Appliquer les filtres"><i class="bi bi-funnel"></i></button>
                </div>
                <div class="col-6 col-lg-1 d-grid">
                    <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary">Effacer</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h2 class="h6 mb-0">Catalogue</h2>
            <span class="text-muted small">{{ $products->total() }} produit(s)</span>
        </div>
        <div class="card-body p-0">
            @if ($products->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Référence</th>
                                <th>Produit</th>
                                <th>Catégorie</th>
                                <th>Prix de vente</th>
                                <th>Stock</th>
                                <th>Statut</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td class="ps-4"><span class="text-muted">{{ $product->reference }}</span></td>
                                    <td><strong>{{ $product->nom }}</strong>@if(auth()->user()?->role === 'admin')<small class="d-block text-muted">Achat : {{ number_format((float) $product->prix_achat, 0, ',', ' ') }} FCFA</small>@endif</td>
                                    <td>{{ $product->category->nom }}</td>
                                    <td class="text-nowrap">{{ number_format((float) $product->prix_vente, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-nowrap">
                                        {{ rtrim(rtrim(number_format((float) $product->quantite_stock, 3, ',', ' '), '0'), ',') }} {{ $product->unit->symbole }}
                                        @if ((float) $product->quantite_stock <= 0)
                                            <span class="badge text-bg-danger ms-1">Rupture</span>
                                        @elseif ((float) $product->quantite_stock <= (float) $product->seuil_alerte)
                                            <span class="badge text-bg-warning ms-1">Stock faible</span>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $product->statut ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $product->statut ? 'Actif' : 'Inactif' }}</span></td>
                                    <td class="text-end pe-4 text-nowrap">@if(auth()->user()?->role === 'admin')
                                        <a href="{{ route('produits.edit', $product) }}" class="btn btn-sm btn-outline-primary" aria-label="Modifier {{ $product->nom }}"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('produits.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce produit ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Supprimer {{ $product->nom }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3">{{ $products->links() }}</div>
            @else
                <div class="text-center py-5 px-3">
                    <i class="bi bi-box-seam display-6 text-muted"></i>
                    <h3 class="h5 mt-3">Aucun produit trouvé</h3>
                    <p class="text-muted">Ajoutez un produit ou modifiez vos critères de recherche.</p>
                    @if(auth()->user()?->role === 'admin')<a href="{{ route('produits.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Ajouter un produit</a>@endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
