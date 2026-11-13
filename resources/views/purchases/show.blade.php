@extends('layouts.app')

@section('title', 'Achat '.$purchase->numero)

@section('content')
@php($remaining = max(0, round((float) $purchase->total - (float) $purchase->montant_paye, 2)))
@php($paidPercent = (float) $purchase->total > 0 ? min(100, round((float) $purchase->montant_paye / (float) $purchase->total * 100)) : 100)
<div class="container-fluid">
    <div class="detail-header no-print">
        <div>
            <a href="{{ route('achats.index') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour aux achats</a>
            <div class="detail-title">
                <h1 class="h3 mb-0">{{ $purchase->numero }}</h1>
                <span class="badge text-bg-success">Réceptionné</span>
                @if ($remaining > 0)<span class="badge text-bg-warning">Solde dû</span>@endif
            </div>
            <p class="text-muted mb-0">{{ $purchase->date_achat->format('d/m/Y à H:i') }} · <a href="{{ route('fournisseurs.show', $purchase->supplier) }}">{{ $purchase->supplier->nom }}</a></p>
        </div>
        <div class="detail-actions">
            <button type="button" class="btn btn-primary" data-sale-print><i class="bi bi-printer me-1" aria-hidden="true"></i>Imprimer</button>
            <a href="{{ route('achats.create') }}" class="btn btn-outline-secondary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nouvel achat</a>
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success no-print">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger no-print">{{ $errors->first() }}</div>@endif

    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-8">
            <article class="sale-receipt card">
                <div class="card-body p-4 p-md-5">
                    <header class="receipt-header">
                        <div class="receipt-brand">
                            <span class="brand-mark"><img src="{{ asset('images/quincapro-icon.svg') }}" alt=""></span>
                            <div><strong>QuincaPro</strong><span>Bon de réception fournisseur</span></div>
                        </div>
                        <div class="receipt-meta">
                            <strong>{{ $purchase->numero }}</strong>
                            <span>{{ $purchase->date_achat->format('d/m/Y à H:i') }}</span>
                            <span class="badge text-bg-success">Réceptionné</span>
                        </div>
                    </header>

                    <div class="receipt-party">
                        <span>Fournisseur</span>
                        <strong>{{ $purchase->supplier->nom }}</strong>
                        @if ($purchase->supplier->contact_nom || $purchase->supplier->telephone)<small>{{ collect([$purchase->supplier->contact_nom, $purchase->supplier->telephone])->filter()->join(' · ') }}</small>@endif
                    </div>

                    <div class="table-responsive">
                        <table class="table receipt-table align-middle">
                            <thead><tr><th>Produit</th><th class="text-end">Qté</th><th class="text-end">Prix unitaire</th><th class="text-end">Total</th></tr></thead>
                            <tbody>
                                @foreach ($purchase->items as $item)
                                    <tr>
                                        <td><strong>{{ $item->nom_produit }}</strong><small class="d-block text-muted">{{ $item->reference_produit }}</small></td>
                                        <td class="text-end text-nowrap">{{ rtrim(rtrim(number_format((float) $item->quantite, 3, ',', ' '), '0'), ',') }} {{ $item->unite_symbole }}</td>
                                        <td class="text-end text-nowrap">{{ number_format((float) $item->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                                        <td class="text-end text-nowrap fw-semibold">{{ number_format((float) $item->total_ligne, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="receipt-totals">
                        <div class="receipt-grand-total"><span>Total de l’achat</span><strong>{{ number_format((float) $purchase->total, 0, ',', ' ') }} FCFA</strong></div>
                        <div><span>Déjà réglé</span><span>{{ number_format((float) $purchase->montant_paye, 0, ',', ' ') }} FCFA</span></div>
                        @if ($remaining > 0)<div class="text-danger"><span>Solde dû au fournisseur</span><strong>{{ number_format($remaining, 0, ',', ' ') }} FCFA</strong></div>@endif
                    </div>

                    <footer class="receipt-footer">Les quantités réceptionnées ont été ajoutées au stock.</footer>
                </div>
            </article>
        </div>

        <aside class="col-12 col-xl-4 no-print detail-side">
            <section class="card">
                <div class="card-header py-3"><h2 class="h6 mb-0"><i class="bi bi-wallet2 me-2" aria-hidden="true"></i>Règlements fournisseur</h2></div>
                <div class="card-body">
                    <div class="settlement-summary">
                        <div><span>Réglé</span><strong>{{ number_format((float) $purchase->montant_paye, 0, ',', ' ') }} FCFA</strong></div>
                        <div class="text-end"><span>Solde dû</span><strong class="{{ $remaining > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($remaining, 0, ',', ' ') }} FCFA</strong></div>
                    </div>
                    <div class="progress settlement-progress" role="progressbar" aria-label="Part réglée" aria-valuenow="{{ $paidPercent }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar {{ $remaining <= 0 ? 'bg-success' : '' }}" style="width: {{ $paidPercent }}%"></div></div>
                    <small class="text-muted">{{ $paidPercent }} % du total réglé</small>

                    @if ($remaining > 0)
                        <form action="{{ route('achats.paiements.store', $purchase) }}" method="POST" class="settlement-form">
                            @csrf
                            <label class="form-label" for="purchase-payment-amount">Nouveau règlement (FCFA)</label>
                            <div class="input-group">
                                <input id="purchase-payment-amount" name="montant" class="form-control" type="number" min="0.01" step="0.01" max="{{ $remaining }}" value="{{ old('montant', $remaining) }}" required>
                                <select id="purchase-payment-method" name="moyen_paiement" class="form-select" aria-label="Moyen de paiement">@foreach ($paymentMethods as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                            </div>
                            <button class="btn btn-primary w-100 mt-2" type="submit"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>Enregistrer le règlement</button>
                        </form>
                    @endif

                    <h3 class="detail-subtitle">Historique</h3>
                    <ul class="timeline-list">
                        @forelse ($purchase->payments as $payment)
                            <li><span class="timeline-dot"></span><div><strong>{{ number_format((float) $payment->montant, 0, ',', ' ') }} FCFA</strong><small>{{ $payment->date_paiement->format('d/m/Y H:i') }} · {{ $paymentMethods[$payment->moyen_paiement] ?? $payment->moyen_paiement }}</small></div></li>
                        @empty
                            <li class="text-muted small">Aucun règlement enregistré.</li>
                        @endforelse
                    </ul>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
