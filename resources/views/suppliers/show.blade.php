@extends('layouts.app')

@section('title', 'Fournisseur '.$supplier->nom)

@section('content')
<div class="container-fluid">
    <a href="{{ route('fournisseurs.index') }}" class="back-link no-print"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour aux fournisseurs</a>

    <section class="card profile-card mt-3 mb-4">
        <div class="card-body">
            <span class="profile-avatar" aria-hidden="true"><i class="bi bi-truck"></i></span>
            <div class="profile-main">
                <div class="detail-title">
                    <h1 class="h3 mb-0">{{ $supplier->nom }}</h1>
                    <span class="badge {{ $supplier->statut ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $supplier->statut ? 'Actif' : 'Inactif' }}</span>
                </div>
                <ul class="profile-contacts">
                    @if ($supplier->contact_nom)<li><i class="bi bi-person" aria-hidden="true"></i><span>{{ $supplier->contact_nom }}</span></li>@endif
                    @if ($supplier->telephone)<li><i class="bi bi-telephone" aria-hidden="true"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $supplier->telephone) }}">{{ $supplier->telephone }}</a></li>@endif
                    @if ($supplier->email)<li><i class="bi bi-envelope" aria-hidden="true"></i><a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a></li>@endif
                    @if ($supplier->adresse)<li><i class="bi bi-geo-alt" aria-hidden="true"></i><span>{{ $supplier->adresse }}</span></li>@endif
                </ul>
            </div>
            <div class="detail-actions">
                @if ($supplier->statut)<a href="{{ route('achats.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary"><i class="bi bi-bag-plus me-1" aria-hidden="true"></i>Nouvel achat</a>@endif
                <a href="{{ route('fournisseurs.edit', $supplier) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Modifier</a>
            </div>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4"><article class="card dashboard-metric metric-orange h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Achats totaux</span><i class="bi bi-bag-check" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ number_format($totalPurchases, 0, ',', ' ') }} <small>FCFA</small></strong><span class="dashboard-metric-note">{{ $supplier->purchases->count() }} réception(s)</span></div></article></div>
        <div class="col-12 col-md-4"><article class="card dashboard-metric metric-green h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Réglé</span><i class="bi bi-cash-coin" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ number_format($totalPaid, 0, ',', ' ') }} <small>FCFA</small></strong></div></article></div>
        <div class="col-12 col-md-4"><article class="card dashboard-metric metric-red h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Solde fournisseur</span><i class="bi bi-hourglass-split" aria-hidden="true"></i></div><strong class="dashboard-metric-value {{ $balance > 0 ? 'text-danger' : '' }}">{{ number_format($balance, 0, ',', ' ') }} <small>FCFA</small></strong><span class="dashboard-metric-note">{{ $balance > 0 ? 'Reste à régler au fournisseur' : 'Aucune dette en cours' }}</span></div></article></div>
    </div>

    <section class="card">
        <div class="card-header d-flex justify-content-between align-items-center py-3"><h2 class="h6 mb-0">Achats réceptionnés</h2><span class="text-muted small">{{ $supplier->purchases->count() }} achat(s)</span></div>
        <div class="card-body p-0">
            @if ($supplier->purchases->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-4">N° d’achat</th><th>Date</th><th>État</th><th class="text-end">Total</th><th class="text-end">Réglé</th><th class="text-end">Solde</th><th class="text-end pe-4"></th></tr></thead>
                        <tbody>
                            @foreach ($supplier->purchases as $purchase)
                                @php($purchaseRemaining = max(0, (float) $purchase->total - (float) $purchase->montant_paye))
                                <tr>
                                    <td class="ps-4"><a href="{{ route('achats.show', $purchase) }}" class="fw-semibold text-decoration-none">{{ $purchase->numero }}</a></td>
                                    <td class="text-nowrap">{{ $purchase->date_achat->format('d/m/Y H:i') }}</td>
                                    <td>@if ($purchaseRemaining > 0)<span class="badge text-bg-warning">Solde dû</span>@else<span class="badge text-bg-success">Réglé</span>@endif</td>
                                    <td class="text-end text-nowrap">{{ number_format((float) $purchase->total, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-end text-nowrap">{{ number_format((float) $purchase->montant_paye, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-end text-nowrap fw-semibold {{ $purchaseRemaining > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($purchaseRemaining, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-end pe-4"><a class="btn btn-sm btn-outline-primary" href="{{ route('achats.show', $purchase) }}" aria-label="Voir l’achat {{ $purchase->numero }}"><i class="bi bi-eye" aria-hidden="true"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 px-3"><i class="bi bi-bag display-6 text-muted" aria-hidden="true"></i><h3 class="h6 mt-3">Aucun achat auprès de ce fournisseur</h3><p class="text-muted mb-0">Les réceptions apparaîtront ici.</p></div>
            @endif
        </div>
    </section>
</div>
@endsection
