@extends('layouts.app')

@section('title', 'Achats')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 mb-1">Achats</h1><p class="text-muted mb-0">Consultez les réceptions fournisseurs et les coûts d’approvisionnement.</p></div><a href="{{ route('achats.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i>Nouvel achat</a></div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="card mb-4"><div class="card-body"><form action="{{ route('achats.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-lg-4"><label for="search" class="form-label">Rechercher</label><input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Numéro d’achat ou fournisseur"></div>
        <div class="col-6 col-lg-3"><label for="date_debut" class="form-label">Du</label><input type="date" name="date_debut" id="date_debut" value="{{ request('date_debut') }}" class="form-control"></div>
        <div class="col-6 col-lg-3"><label for="date_fin" class="form-label">Au</label><input type="date" name="date_fin" id="date_fin" value="{{ request('date_fin') }}" class="form-control"></div>
        <div class="col-6 col-lg-1 d-grid"><button type="submit" class="btn btn-dark"><i class="bi bi-funnel" aria-hidden="true"></i>Filtrer</button></div>
        <div class="col-6 col-lg-1 d-grid"><a href="{{ route('achats.index') }}" class="btn btn-outline-secondary">Effacer</a></div>
    </form></div></div>
    <div class="card"><div class="card-header d-flex justify-content-between align-items-center py-3"><h2 class="h6 mb-0">Historique des réceptions</h2><span class="text-muted small">{{ $purchases->total() }} achat(s)</span></div><div class="card-body p-0">
        @if ($purchases->count())
            <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th class="ps-4">N° achat</th><th>Date</th><th>Fournisseur</th><th>Articles</th><th>Total</th><th>État</th><th class="text-end pe-4">Détail</th></tr></thead><tbody>
                @foreach ($purchases as $purchase)
                    <tr><td class="ps-4"><a href="{{ route('achats.show', $purchase) }}" class="fw-semibold text-decoration-none">{{ $purchase->numero }}</a></td><td class="text-nowrap">{{ $purchase->date_achat->format('d/m/Y H:i') }}</td><td>{{ $purchase->supplier->nom }}</td><td>{{ $purchase->items_count }}</td><td class="text-nowrap fw-semibold">{{ number_format((float) $purchase->total, 0, ',', ' ') }} FCFA</td><td><span class="badge text-bg-success">Réceptionné</span></td><td class="text-end pe-4"><a href="{{ route('achats.show', $purchase) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye" aria-hidden="true"></i>Voir</a></td></tr>
                @endforeach
            </tbody></table></div><div class="px-4 py-3">{{ $purchases->links() }}</div>
        @else
            <div class="text-center py-5 px-3"><i class="bi bi-bag display-6 text-muted" aria-hidden="true"></i><h3 class="h5 mt-3">Aucun achat enregistré</h3><p class="text-muted">Enregistrez une réception pour augmenter le stock et conserver son coût.</p><a href="{{ route('achats.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i>Enregistrer un achat</a></div>
        @endif
    </div></div>
</div>
@endsection
