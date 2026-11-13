@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">Clients</h1><p class="text-muted mb-0">Centralisez les coordonnées et retrouvez les ventes de vos clients.</p></div>
        <a href="{{ route('clients.create') }}" class="btn btn-primary"><i class="bi bi-person-plus" aria-hidden="true"></i>Nouveau client</a>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card mb-4"><div class="card-body">
        <form action="{{ route('clients.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-7"><label for="search" class="form-label">Rechercher un client</label><div class="input-group"><span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span><input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, téléphone ou courriel"></div></div>
            <div class="col-8 col-md-3"><label for="statut" class="form-label">Statut</label><select name="statut" id="statut" class="form-select"><option value="">Tous</option><option value="1" @selected(request('statut') === '1')>Actifs</option><option value="0" @selected(request('statut') === '0')>Inactifs</option></select></div>
            <div class="col-4 col-md-2 d-grid"><button type="submit" class="btn btn-dark"><i class="bi bi-funnel" aria-hidden="true"></i>Filtrer</button></div>
        </form>
    </div></div>

    <div class="card"><div class="card-header d-flex justify-content-between align-items-center py-3"><h2 class="h6 mb-0">Répertoire clients</h2><span class="text-muted small">{{ $customers->total() }} client(s)</span></div><div class="card-body p-0">
        @if ($customers->count())
            <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th class="ps-4">Client</th><th>Téléphone</th><th>Courriel</th><th>Ventes</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead><tbody>
                @foreach ($customers as $customer)
                    <tr><td class="ps-4"><a href="{{ route('clients.show', $customer) }}" class="fw-semibold text-decoration-none">{{ $customer->nom }}</a><small class="d-block text-muted">{{ $customer->adresse }}</small></td><td>{{ $customer->telephone ?: '—' }}</td><td>{{ $customer->email ?: '—' }}</td><td>{{ $customer->sales_count }}</td><td><span class="badge {{ $customer->statut ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $customer->statut ? 'Actif' : 'Inactif' }}</span></td><td class="text-end pe-4 text-nowrap">@if(auth()->user()?->role === 'admin')<a href="{{ route('clients.edit', $customer) }}" class="btn btn-sm btn-outline-primary" aria-label="Modifier {{ $customer->nom }}"><i class="bi bi-pencil" aria-hidden="true"></i></a><form action="{{ route('clients.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce client ?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" @disabled($customer->sales_count > 0) title="{{ $customer->sales_count > 0 ? 'Client lié à des ventes, désactivez sa fiche' : 'Supprimer ce client' }}"><i class="bi bi-trash" aria-hidden="true"></i></button></form>@endif</td></tr>
                @endforeach
            </tbody></table></div><div class="px-4 py-3">{{ $customers->links() }}</div>
        @else
            <div class="text-center py-5 px-3"><i class="bi bi-people display-6 text-muted" aria-hidden="true"></i><h3 class="h5 mt-3">Aucun client trouvé</h3><p class="text-muted">Ajoutez une fiche client pour la retrouver facilement au moment d’une vente.</p><a href="{{ route('clients.create') }}" class="btn btn-primary"><i class="bi bi-person-plus" aria-hidden="true"></i>Ajouter un client</a></div>
        @endif
    </div></div>
</div>
@endsection
