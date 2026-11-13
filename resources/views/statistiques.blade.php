@extends('layouts.app')

@section('title', 'Rapports')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><span class="dashboard-eyebrow">ANALYSE DE L’ACTIVITÉ</span><h1 class="h3 mb-1">Rapports</h1><p class="text-muted mb-0">Suivez les ventes, les achats et les produits les plus vendus sur la période.</p></div><a class="btn btn-outline-primary" href="{{ route('rapports.export', request()->only('date_debut', 'date_fin')) }}"><i class="bi bi-download me-1"></i>Exporter en CSV</a></div>

    <div class="card mb-4"><div class="card-body"><form action="{{ route('statistiques') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-6 col-md-4"><label for="date_debut" class="form-label">Date de début</label><input type="date" id="date_debut" name="date_debut" value="{{ $dateStart->toDateString() }}" class="form-control"></div>
        <div class="col-6 col-md-4"><label for="date_fin" class="form-label">Date de fin</label><input type="date" id="date_fin" name="date_fin" value="{{ $dateEnd->toDateString() }}" class="form-control"></div>
        <div class="col-12 col-md-4 d-flex gap-2"><button type="submit" class="btn btn-dark flex-grow-1"><i class="bi bi-funnel" aria-hidden="true"></i>Appliquer</button><a href="{{ route('statistiques') }}" class="btn btn-outline-secondary">Ce mois</a></div>
    </form></div></div>

    <div class="row g-3 mb-4 report-metrics">
        <div class="col-12 col-sm-6 col-xl-4"><article class="card dashboard-metric metric-orange h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Chiffre d’affaires</span><i class="bi bi-currency-exchange" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ number_format($salesTotal, 0, ',', ' ') }} <small>FCFA</small></strong><span class="dashboard-metric-note">Ventes enregistrées, retours déduits</span></div></article></div>
        <div class="col-12 col-sm-6 col-xl-4"><article class="card dashboard-metric metric-blue h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Montant encaissé net</span><i class="bi bi-cash-coin" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ number_format($collectedTotal, 0, ',', ' ') }} <small>FCFA</small></strong><span class="dashboard-metric-note">Règlements reçus, retours déduits</span></div></article></div>
        <div class="col-12 col-sm-6 col-xl-4"><article class="card dashboard-metric metric-green h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Ventes enregistrées</span><i class="bi bi-receipt" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ number_format($salesCount, 0, ',', ' ') }}</strong><span class="dashboard-metric-note">Tous les états de règlement</span></div></article></div>
        <div class="col-12 col-sm-6 col-xl-4"><article class="card dashboard-metric metric-green h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Panier moyen</span><i class="bi bi-basket" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ number_format($averageSale, 0, ',', ' ') }} <small>FCFA</small></strong><span class="dashboard-metric-note">Montant moyen par vente</span></div></article></div>
        <div class="col-12 col-sm-6 col-xl-4"><article class="card dashboard-metric metric-red h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Achats réceptionnés</span><i class="bi bi-bag-check" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ number_format($purchasesTotal, 0, ',', ' ') }} <small>FCFA</small></strong><span class="dashboard-metric-note">Solde dû : {{ number_format($purchasesDue, 0, ',', ' ') }} FCFA</span></div></article></div>
        <div class="col-12 col-sm-6 col-xl-4"><article class="card dashboard-metric metric-green h-100"><div class="card-body"><div class="dashboard-metric-top"><span>Marge brute estimée</span><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></div><strong class="dashboard-metric-value">{{ number_format($marginTotal, 0, ',', ' ') }} <small>FCFA</small></strong><span class="dashboard-metric-note">{{ $untrackedCostLineCount ? 'Coût d’achat absent sur '.$untrackedCostLineCount.' ligne(s) historique(s)' : 'Marge calculée avec le coût enregistré à la vente' }}</span></div></article></div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-7"><section class="card h-100"><div class="card-header d-flex justify-content-between align-items-center py-3"><h2 class="h6 mb-0">Évolution par jour</h2><span class="text-muted small">{{ $dateStart->format('d/m/Y') }} – {{ $dateEnd->format('d/m/Y') }}</span></div><div class="card-body p-0">
            @if ($dailyReport->isNotEmpty())
                <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th class="ps-4">Jour</th><th>Ventes</th><th>Chiffre d’affaires</th><th class="text-end pe-4">Achats</th></tr></thead><tbody>@foreach ($dailyReport as $day)<tr><td class="ps-4">{{ \Illuminate\Support\Carbon::parse($day['jour'])->format('d/m/Y') }}</td><td>{{ $day['nombre_ventes'] }}</td><td class="fw-semibold text-nowrap">{{ number_format($day['chiffre_affaires'], 0, ',', ' ') }} FCFA</td><td class="text-end pe-4 text-nowrap">{{ number_format($day['total_achats'], 0, ',', ' ') }} FCFA</td></tr>@endforeach</tbody></table></div>
            @else
                <div class="text-center py-5 px-3"><i class="bi bi-bar-chart display-6 text-muted" aria-hidden="true"></i><h3 class="h6 mt-3">Aucune activité sur cette période</h3><p class="text-muted mb-0">Les ventes et achats apparaîtront ici dès leur enregistrement.</p></div>
            @endif
        </div></section></div>
        <div class="col-12 col-xl-5"><section class="card h-100"><div class="card-header py-3"><h2 class="h6 mb-0">Produits les plus vendus</h2></div><div class="card-body p-0">
            @if ($topProducts->isNotEmpty())
                <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th class="ps-4">Produit</th><th>Quantité</th><th class="text-end pe-4">Ventes</th></tr></thead><tbody>@foreach ($topProducts as $product)<tr><td class="ps-4"><strong>{{ $product->nom_produit }}</strong><small class="d-block text-muted">{{ $product->reference_produit }}</small></td><td>{{ rtrim(rtrim(number_format((float) $product->quantite_vendue, 3, ',', ' '), '0'), ',') }}</td><td class="text-end pe-4 text-nowrap">{{ number_format((float) $product->chiffre_affaires, 0, ',', ' ') }} FCFA</td></tr>@endforeach</tbody></table></div>
            @else
                <div class="text-center py-5 px-3"><i class="bi bi-box-seam display-6 text-muted" aria-hidden="true"></i><h3 class="h6 mt-3">Aucun produit vendu</h3><p class="text-muted mb-0">Le classement sera disponible avec les premières ventes.</p></div>
            @endif
        </div></section></div>

        <div class="col-12"><section class="card"><div class="card-header py-3"><h2 class="h6 mb-0">Ventes par moyen de paiement</h2></div><div class="card-body p-0">
            @if ($paymentTotals->isNotEmpty())
                <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th class="ps-4">Moyen de paiement</th><th>Nombre de règlements</th><th class="text-end pe-4">Montant total</th></tr></thead><tbody>@foreach ($paymentTotals as $payment)<tr><td class="ps-4">{{ $paymentMethods[$payment->moyen_paiement] ?? $payment->moyen_paiement }}</td><td>{{ $payment->nombre_reglements }}</td><td class="text-end pe-4 fw-semibold">{{ number_format((float) $payment->chiffre_affaires, 0, ',', ' ') }} FCFA</td></tr>@endforeach</tbody></table></div>
            @else
                <div class="p-4 text-muted small">Aucun paiement enregistré sur cette période.</div>
            @endif
        </div></section></div>
    </div>
</div>
@endsection
