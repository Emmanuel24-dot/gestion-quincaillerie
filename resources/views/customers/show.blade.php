@extends('layouts.app')

@section('title', 'Client '.$customer->nom)

@section('content')
<div class="container-fluid">
    <a href="{{ route('clients.index') }}" class="back-link no-print"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour aux clients</a>

    <section class="card profile-card mt-3 mb-4">
        <div class="card-body">
            <span class="profile-avatar" aria-hidden="true">{{ Str::upper(Str::substr($customer->nom, 0, 2)) }}</span>
            <div class="profile-main">
                <div class="detail-title">
                    <h1 class="h3 mb-0">{{ $customer->nom }}</h1>
                    <span class="badge {{ $customer->statut ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $customer->statut ? 'Actif' : 'Inactif' }}</span>
                </div>
                <ul class="profile-contacts">
                    @if ($customer->telephone)<li><i class="bi bi-telephone" aria-hidden="true"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $customer->telephone) }}">{{ $customer->telephone }}</a></li>@endif
                    @if ($customer->email)<li><i class="bi bi-envelope" aria-hidden="true"></i><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></li>@endif
                    @if ($customer->adresse)<li><i class="bi bi-geo-alt" aria-hidden="true"></i><span>{{ $customer->adresse }}</span></li>@endif
                    <li><i class="bi bi-calendar3" aria-hidden="true"></i><span>Client depuis le {{ $customer->created_at?->format('d/m/Y') }}</span></li>
                </ul>
            </div>
            <div class="detail-actions">
                @if ($customer->statut)<a href="{{ route('ventes.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary"><i class="bi bi-cart-plus me-1" aria-hidden="true"></i>Nouvelle vente</a>@endif
                @if (auth()->user()?->role === 'admin')<a href="{{ route('clients.edit', $customer) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Modifier</a>@endif
            </div>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3"><article class="card dashboard-metric metric-orange h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Ventes totales</span><i class="bi bi-receipt" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ number_format($totalSales, 0, ',', ' ') }} <small>FCFA</small></strong><span class="dashboard-metric-note">{{ $customer->sales->count() }} vente(s)</span></div></article></div>
        <div class="col-12 col-sm-6 col-xl-3"><article class="card dashboard-metric metric-green h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Paiements reçus</span><i class="bi bi-cash-coin" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ number_format($totalPaid, 0, ',', ' ') }} <small>FCFA</small></strong></div></article></div>
        <div class="col-12 col-sm-6 col-xl-3"><article class="card dashboard-metric metric-blue h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Retours remboursés</span><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ number_format($totalReturned, 0, ',', ' ') }} <small>FCFA</small></strong></div></article></div>
        <div class="col-12 col-sm-6 col-xl-3"><article class="card dashboard-metric metric-red h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Solde restant dû</span><i class="bi bi-hourglass-split" aria-hidden="true"></i></div><strong class="dashboard-metric-value {{ $balance > 0 ? 'text-danger' : '' }}">{{ number_format($balance, 0, ',', ' ') }} <small>FCFA</small></strong><span class="dashboard-metric-note">{{ $balance > 0 ? 'À encaisser auprès du client' : 'Aucune dette en cours' }}</span></div></article></div>
    </div>

    <section class="card">
        <div class="card-header d-flex justify-content-between align-items-center py-3"><h2 class="h6 mb-0">Historique des ventes</h2><span class="text-muted small">{{ $customer->sales->count() }} vente(s)</span></div>
        <div class="card-body p-0">
            @if ($customer->sales->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-4">N° de vente</th><th>Date</th><th>État</th><th class="text-end">Total</th><th class="text-end">Payé</th><th class="text-end">Reste</th><th class="text-end pe-4"></th></tr></thead>
                        <tbody>
                            @foreach ($customer->sales as $sale)
                                @php($saleRemaining = max(0, (float) $sale->total - (float) $sale->montant_paye))
                                <tr>
                                    <td class="ps-4"><a href="{{ route('ventes.show', $sale) }}" class="fw-semibold text-decoration-none">{{ $sale->numero }}</a></td>
                                    <td class="text-nowrap">{{ $sale->date_vente->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge {{ $sale->statut_badge_class }}">{{ $sale->statut_label }}</span>@if ($sale->returns->isNotEmpty())<span class="badge text-bg-light border ms-1">Retour</span>@endif</td>
                                    <td class="text-end text-nowrap">{{ number_format((float) $sale->total, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-end text-nowrap">{{ number_format((float) $sale->montant_paye, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-end text-nowrap fw-semibold {{ $saleRemaining > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($saleRemaining, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-end pe-4"><a class="btn btn-sm btn-outline-primary" href="{{ route('ventes.show', $sale) }}" aria-label="Voir la vente {{ $sale->numero }}"><i class="bi bi-eye" aria-hidden="true"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 px-3"><i class="bi bi-receipt display-6 text-muted" aria-hidden="true"></i><h3 class="h6 mt-3">Aucune vente pour ce client</h3><p class="text-muted mb-0">Ses achats apparaîtront ici dès la première vente.</p></div>
            @endif
        </div>
    </section>
</div>
@endsection
