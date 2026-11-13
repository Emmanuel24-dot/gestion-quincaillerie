@extends('layouts.app')

@section('title', 'Vente '.$sale->numero)

@section('content')
@php($remaining = max(0, round((float) $sale->total - (float) $sale->montant_paye, 2)))
@php($paidPercent = (float) $sale->total > 0 ? min(100, round((float) $sale->montant_paye / (float) $sale->total * 100)) : 100)
@php($returnableItems = $sale->items->filter(fn ($item) => (float) $item->quantite - (float) $item->returns->sum('quantite') > 0))
@php($refundableAmount = (float) $sale->montant_paye - (float) $sale->returns->sum('total'))
<div class="container-fluid">
    <div class="detail-header no-print">
        <div>
            <a href="{{ route('ventes.index') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour aux ventes</a>
            <div class="detail-title">
                <h1 class="h3 mb-0">{{ $sale->numero }}</h1>
                <span class="badge {{ $sale->statut_badge_class }}">{{ $sale->statut_label }}</span>
            </div>
            <p class="text-muted mb-0">{{ $sale->date_vente->format('d/m/Y à H:i') }} · {{ $sale->client_nom ?: 'Client de passage' }}</p>
        </div>
        <div class="detail-actions">
            @if ($sale->invoice)
                <a href="{{ route('factures.show', $sale->invoice) }}" class="btn btn-outline-primary"><i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>Voir la facture</a>
            @elseif ($remaining <= 0)
                <form action="{{ route('factures.store', $sale) }}" method="POST">@csrf<button type="submit" class="btn btn-outline-primary"><i class="bi bi-file-earmark-plus me-1" aria-hidden="true"></i>Émettre une facture</button></form>
            @endif
            <button type="button" class="btn btn-primary" data-sale-print><i class="bi bi-printer me-1" aria-hidden="true"></i>Imprimer le reçu</button>
            <a href="{{ route('ventes.create') }}" class="btn btn-outline-secondary"><i class="bi bi-cart-plus me-1" aria-hidden="true"></i>Nouvelle vente</a>
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
                            <div><strong>QuincaPro</strong><span>Reçu de vente</span></div>
                        </div>
                        <div class="receipt-meta">
                            <strong>{{ $sale->numero }}</strong>
                            <span>{{ $sale->date_vente->format('d/m/Y à H:i') }}</span>
                            <span class="badge {{ $sale->statut_badge_class }}">{{ $sale->statut_label }}</span>
                        </div>
                    </header>

                    <div class="receipt-party">
                        <span>Client</span>
                        <strong>{{ $sale->client_nom ?: 'Client de passage' }}</strong>
                        @if ($sale->customer?->telephone)<small>{{ $sale->customer->telephone }}</small>@endif
                    </div>

                    <div class="table-responsive">
                        <table class="table receipt-table align-middle">
                            <thead><tr><th>Article</th><th class="text-end">Qté</th><th class="text-end">Prix unitaire</th><th class="text-end">Total</th></tr></thead>
                            <tbody>
                                @foreach ($sale->items as $item)
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
                        <div><span>Sous-total</span><span>{{ number_format((float) $sale->sous_total, 0, ',', ' ') }} FCFA</span></div>
                        <div class="receipt-grand-total"><span>Total</span><strong>{{ number_format((float) $sale->total, 0, ',', ' ') }} FCFA</strong></div>
                        <div><span>Reçu ({{ $paymentMethods[$sale->moyen_paiement] ?? $sale->moyen_paiement }})</span><span>{{ number_format((float) $sale->montant_recu, 0, ',', ' ') }} FCFA</span></div>
                        @if ((float) $sale->monnaie_rendue > 0)<div><span>Monnaie rendue</span><span>{{ number_format((float) $sale->monnaie_rendue, 0, ',', ' ') }} FCFA</span></div>@endif
                        <div><span>Total encaissé</span><strong>{{ number_format((float) $sale->montant_paye, 0, ',', ' ') }} FCFA</strong></div>
                        @if ($remaining > 0)<div class="text-danger"><span>Reste à payer</span><strong>{{ number_format($remaining, 0, ',', ' ') }} FCFA</strong></div>@endif
                    </div>

                    @if ($sale->returns->isNotEmpty())
                        <div class="receipt-returns">
                            <span>Retours</span>
                            @foreach ($sale->returns as $return)
                                <div><span>{{ $return->numero }} · {{ $return->date_retour->format('d/m/Y') }} · {{ $return->motif }}</span><strong>−{{ number_format((float) $return->total, 0, ',', ' ') }} FCFA</strong></div>
                            @endforeach
                        </div>
                    @endif

                    <footer class="receipt-footer">Merci pour votre achat et à bientôt chez QuincaPro.</footer>
                </div>
            </article>
        </div>

        <aside class="col-12 col-xl-4 no-print detail-side">
            <section class="card">
                <div class="card-header py-3"><h2 class="h6 mb-0"><i class="bi bi-wallet2 me-2" aria-hidden="true"></i>Règlement</h2></div>
                <div class="card-body">
                    <div class="settlement-summary">
                        <div><span>Encaissé</span><strong>{{ number_format((float) $sale->montant_paye, 0, ',', ' ') }} FCFA</strong></div>
                        <div class="text-end"><span>Reste</span><strong class="{{ $remaining > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($remaining, 0, ',', ' ') }} FCFA</strong></div>
                    </div>
                    <div class="progress settlement-progress" role="progressbar" aria-label="Part réglée" aria-valuenow="{{ $paidPercent }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar {{ $remaining <= 0 ? 'bg-success' : '' }}" style="width: {{ $paidPercent }}%"></div></div>
                    <small class="text-muted">{{ $paidPercent }} % du total réglé</small>

                    @if ($remaining > 0)
                        <form action="{{ route('ventes.paiements.store', $sale) }}" method="POST" class="settlement-form">
                            @csrf
                            <label class="form-label" for="payment-amount">Nouveau paiement (FCFA)</label>
                            <div class="input-group">
                                <input id="payment-amount" name="montant" class="form-control" type="number" min="0.01" step="0.01" max="{{ $remaining }}" value="{{ old('montant', $remaining) }}" required>
                                <select id="payment-method" name="moyen_paiement" class="form-select" aria-label="Moyen de paiement">@foreach ($paymentMethods as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                            </div>
                            <button class="btn btn-primary w-100 mt-2" type="submit"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>Enregistrer le paiement</button>
                        </form>
                    @endif

                    <h3 class="detail-subtitle">Historique</h3>
                    <ul class="timeline-list">
                        @forelse ($sale->payments as $payment)
                            <li><span class="timeline-dot"></span><div><strong>{{ number_format((float) $payment->montant, 0, ',', ' ') }} FCFA</strong><small>{{ $payment->date_paiement->format('d/m/Y H:i') }} · {{ $paymentMethods[$payment->moyen_paiement] ?? $payment->moyen_paiement }}</small></div></li>
                        @empty
                            <li class="text-muted small">Aucun paiement enregistré.</li>
                        @endforelse
                    </ul>
                </div>
            </section>

            @if (auth()->user()?->role === 'admin' && $returnableItems->isNotEmpty() && $refundableAmount > 0)
                <section class="card mt-4">
                    <div class="card-header py-3"><h2 class="h6 mb-0"><i class="bi bi-arrow-counterclockwise me-2" aria-hidden="true"></i>Retour / remboursement</h2></div>
                    <div class="card-body">
                        <p class="text-muted small">Remboursable au maximum : <strong>{{ number_format($refundableAmount, 0, ',', ' ') }} FCFA</strong>. Le stock est réintégré automatiquement.</p>
                        <form action="{{ route('ventes.retours.store', $sale) }}" method="POST" class="row g-2">
                            @csrf
                            <div class="col-12"><label class="form-label" for="return-item">Article</label><select class="form-select" id="return-item" name="items[0][sale_item_id]" required>@foreach ($returnableItems as $item)<option value="{{ $item->id }}" @selected(old('items.0.sale_item_id') == $item->id)>{{ $item->nom_produit }} — {{ rtrim(rtrim(number_format((float) $item->quantite - (float) $item->returns->sum('quantite'), 3, ',', ' '), '0'), ',') }} {{ $item->unite_symbole }} max</option>@endforeach</select></div>
                            <div class="col-6"><label class="form-label" for="return-quantity">Quantité</label><input id="return-quantity" class="form-control" name="items[0][quantite]" type="number" min="0.001" step="0.001" value="{{ old('items.0.quantite', 1) }}" required></div>
                            <div class="col-6"><label class="form-label" for="return-method">Remboursement</label><select class="form-select" id="return-method" name="moyen_remboursement">@foreach ($paymentMethods as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                            <div class="col-12"><label class="form-label" for="return-reason">Motif</label><input id="return-reason" class="form-control" name="motif" maxlength="255" value="{{ old('motif') }}" placeholder="Ex. article défectueux" required></div>
                            <div class="col-12"><button class="btn btn-outline-danger w-100" type="submit" onclick="return confirm('Enregistrer ce retour et rembourser le client ?');">Enregistrer le retour</button></div>
                        </form>
                    </div>
                </section>
            @endif
        </aside>
    </div>
</div>
@endsection
