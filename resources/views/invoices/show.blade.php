@extends('layouts.app')

@section('title', 'Facture '.$invoice->numero)

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div><a href="{{ route('factures.index') }}" class="back-link"><i class="bi bi-arrow-left me-1"></i>Retour à la facturation</a><h1 class="h3 mt-2 mb-0">Facture</h1></div>
        <button type="button" class="btn btn-outline-primary" data-invoice-print><i class="bi bi-printer me-1"></i>Imprimer la facture</button>
    </div>

    <article class="sale-receipt card border-0 shadow-sm mx-auto">
        <div class="card-body p-4 p-md-5">
            <header class="d-flex flex-wrap justify-content-between gap-3 border-bottom pb-4 mb-4">
                <div><div class="d-flex align-items-center gap-2 mb-2"><span class="brand-mark"><img src="{{ asset('images/quincapro-icon.svg') }}" alt=""></span><strong class="fs-5">QuincaPro</strong></div><span class="text-muted">Facture de vente</span></div>
                <div class="text-md-end"><strong class="d-block">{{ $invoice->numero }}</strong><span class="text-muted">Émise le {{ $invoice->date_emission->format('d/m/Y à H:i') }}</span></div>
            </header>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6"><span class="text-muted small d-block">Client</span><strong>{{ $invoice->sale->client_nom ?: 'Client de passage' }}</strong></div>
                <div class="col-12 col-md-6 text-md-end"><span class="text-muted small d-block">Vente associée</span><strong>{{ $invoice->sale->numero }}</strong><span class="d-block text-muted">{{ $invoice->sale->date_vente->format('d/m/Y à H:i') }}</span></div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Article</th><th class="text-end">Qté</th><th class="text-end">Prix unitaire</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        @foreach ($invoice->sale->items as $item)
                            <tr><td><strong>{{ $item->nom_produit }}</strong><small class="d-block text-muted">{{ $item->reference_produit }}</small></td><td class="text-end text-nowrap">{{ number_format((float) $item->quantite, 3, ',', ' ') }} {{ $item->unite_symbole }}</td><td class="text-end text-nowrap">{{ number_format((float) $item->prix_unitaire, 0, ',', ' ') }} FCFA</td><td class="text-end text-nowrap">{{ number_format((float) $item->total_ligne, 0, ',', ' ') }} FCFA</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end mt-4"><div class="col-12 col-md-6 col-lg-5">
                <div class="d-flex justify-content-between py-2"><span>Sous-total</span><span>{{ number_format((float) $invoice->sale->sous_total, 0, ',', ' ') }} FCFA</span></div>
                <div class="d-flex justify-content-between border-top py-2 fw-bold fs-5"><span>Total facture</span><span>{{ number_format((float) $invoice->sale->total, 0, ',', ' ') }} FCFA</span></div>
                <div class="d-flex justify-content-between py-1 text-muted"><span>Total réglé</span><span>{{ number_format((float) $invoice->sale->montant_paye, 0, ',', ' ') }} FCFA</span></div>
                <div class="d-flex justify-content-between py-1 text-muted"><span>Moyen de paiement</span><span>{{ $paymentMethods[$invoice->sale->moyen_paiement] ?? $invoice->sale->moyen_paiement }}</span></div>
            </div></div>

            <footer class="border-top mt-4 pt-3 text-center text-muted small">Document établi à partir de la vente {{ $invoice->sale->numero }}.</footer>
        </div>
    </article>
</div>
@endsection
