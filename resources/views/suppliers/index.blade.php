@extends('layouts.app')

@section('title', 'Fournisseurs')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 mb-1">Fournisseurs</h1><p class="text-muted mb-0">Gérez vos partenaires et retrouvez l’historique des réceptions.</p></div><a href="{{ route('fournisseurs.create') }}" class="btn btn-primary"><i class="bi bi-truck" aria-hidden="true"></i>Nouveau fournisseur</a></div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="card mb-4"><div class="card-body"><form action="{{ route('fournisseurs.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-md-7"><label for="search" class="form-label">Rechercher un fournisseur</label><div class="input-group"><span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span><input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Entreprise, contact, téléphone ou courriel"></div></div>
        <div class="col-8 col-md-3"><label for="statut" class="form-label">Statut</label><select name="statut" id="statut" class="form-select"><option value="">Tous</option><option value="1" @selected(request('statut') === '1')>Actifs</option><option value="0" @selected(request('statut') === '0')>Inactifs</option></select></div>
        <div class="col-4 col-md-2 d-grid"><button type="submit" class="btn btn-dark"><i class="bi bi-funnel" aria-hidden="true"></i>Filtrer</button></div>
    </form></div></div>
    <div class="card"><div class="card-header d-flex justify-content-between align-items-center py-3"><h2 class="h6 mb-0">Répertoire fournisseurs</h2><span class="text-muted small">{{ $suppliers->total() }} fournisseur(s)</span></div><div class="card-body p-0">
        @if ($suppliers->count())
            <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th class="ps-4">Entreprise</th><th>Contact</th><th>Téléphone</th><th>Courriel</th><th>Achats</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead><tbody>
                @foreach ($suppliers as $supplier)
                    <tr><td class="ps-4"><a href="{{ route('fournisseurs.show', $supplier) }}" class="fw-semibold text-decoration-none">{{ $supplier->nom }}</a><small class="d-block text-muted">{{ $supplier->adresse }}</small></td><td>{{ $supplier->contact_nom ?: '—' }}</td><td>{{ $supplier->telephone ?: '—' }}</td><td>{{ $supplier->email ?: '—' }}</td><td>{{ $supplier->purchases_count }}</td><td><span class="badge {{ $supplier->statut ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $supplier->statut ? 'Actif' : 'Inactif' }}</span></td><td class="text-end pe-4 text-nowrap"><a href="{{ route('fournisseurs.edit', $supplier) }}" class="btn btn-sm btn-outline-primary" aria-label="Modifier {{ $supplier->nom }}"><i class="bi bi-pencil" aria-hidden="true"></i></a><form action="{{ route('fournisseurs.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce fournisseur ?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" @disabled($supplier->purchases_count > 0) title="{{ $supplier->purchases_count > 0 ? 'Fournisseur lié à des achats, désactivez sa fiche' : 'Supprimer ce fournisseur' }}"><i class="bi bi-trash" aria-hidden="true"></i></button></form></td></tr>
                @endforeach
            </tbody></table></div><div class="px-4 py-3">{{ $suppliers->links() }}</div>
        @else
            <div class="text-center py-5 px-3"><i class="bi bi-truck display-6 text-muted" aria-hidden="true"></i><h3 class="h5 mt-3">Aucun fournisseur trouvé</h3><p class="text-muted">Créez une fiche fournisseur pour enregistrer vos prochains achats.</p><a href="{{ route('fournisseurs.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i>Ajouter un fournisseur</a></div>
        @endif
    </div></div>
</div>
@endsection
