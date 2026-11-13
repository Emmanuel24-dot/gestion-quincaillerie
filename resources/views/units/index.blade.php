@extends('layouts.app')

@section('title', 'Unités')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Unités</h1>
            <p class="text-muted mb-0">Définissez les unités utilisées pour mesurer vos produits.</p>
        </div>
        <a href="{{ route('unites.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nouvelle unité</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('unites.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-7">
                    <label for="search" class="form-label">Rechercher une unité</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Nom ou symbole">
                    </div>
                </div>
                <div class="col-8 col-md-3">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select">
                        <option value="">Tous</option>
                        <option value="1" @selected(request('statut') === '1')>Actives</option>
                        <option value="0" @selected(request('statut') === '0')>Inactives</option>
                    </select>
                </div>
                <div class="col-4 col-md-2 d-grid">
                    <button type="submit" class="btn btn-dark"><i class="bi bi-funnel me-1"></i>Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h2 class="h6 mb-0">Unités enregistrées</h2>
            <span class="text-muted small">{{ $units->total() }} unité(s)</span>
        </div>
        <div class="card-body p-0">
            @if ($units->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nom</th>
                                <th>Symbole</th>
                                <th>Produits associés</th>
                                <th>Statut</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($units as $unit)
                                <tr>
                                    <td class="ps-4"><strong>{{ $unit->nom }}</strong></td>
                                    <td><span class="badge text-bg-light border">{{ $unit->symbole }}</span></td>
                                    <td>{{ $unit->products_count }}</td>
                                    <td><span class="badge {{ $unit->statut ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $unit->statut ? 'Active' : 'Inactive' }}</span></td>
                                    <td class="text-end pe-4 text-nowrap">
                                        <a href="{{ route('unites.edit', $unit) }}" class="btn btn-sm btn-outline-primary" aria-label="Modifier {{ $unit->nom }}"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('unites.destroy', $unit) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette unité ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" @disabled($unit->products_count > 0) title="{{ $unit->products_count > 0 ? 'Cette unité est utilisée par des produits' : 'Supprimer cette unité' }}"><i class="bi bi-trash me-1" aria-hidden="true"></i>Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3">{{ $units->links() }}</div>
            @else
                <div class="text-center py-5 px-3">
                    <i class="bi bi-rulers display-6 text-muted"></i>
                    <h3 class="h5 mt-3">Aucune unité enregistrée</h3>
                    <p class="text-muted">Ajoutez des unités comme pièce, kilogramme ou mètre pour les choisir sur vos produits.</p>
                    <a href="{{ route('unites.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Ajouter une unité</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
