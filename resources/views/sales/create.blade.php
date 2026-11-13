@extends('layouts.app')

@section('title', 'Nouvelle vente')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <a href="{{ route('ventes.index') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour aux ventes</a>
            <h1 class="h3 mt-3 mb-1">Caisse</h1>
            <p class="text-muted mb-0">Touchez un produit pour l’ajouter au panier, puis encaissez.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger"><strong>La vente n’a pas été enregistrée.</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if ($products->isEmpty())
        <div class="card"><div class="card-body text-center py-5"><i class="bi bi-box-seam display-6 text-muted" aria-hidden="true"></i><h2 class="h5 mt-3">Aucun produit en stock</h2><p class="text-muted">La caisse affiche uniquement les produits actifs disponibles en stock.</p><a href="{{ route('stock.index') }}" class="btn btn-outline-primary">Consulter le stock</a></div></div>
    @else
        <form action="{{ route('ventes.store') }}" method="POST" class="pos-layout" data-pos-form data-pos-initial-items="{{ json_encode(array_values(old('items', []))) }}">
            @csrf

            <section class="card pos-catalog" aria-labelledby="pos-catalog-title">
                <div class="pos-catalog-toolbar">
                    <h2 class="visually-hidden" id="pos-catalog-title">Produits disponibles</h2>
                    <div class="pos-search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" class="form-control" placeholder="Rechercher par nom ou référence…" aria-label="Rechercher un produit" autocomplete="off" data-pos-search autofocus>
                        <kbd class="pos-search-hint">Entrée</kbd>
                    </div>
                    <div class="pos-categories" role="group" aria-label="Filtrer par catégorie">
                        <button type="button" class="pos-chip is-active" data-pos-category="" aria-pressed="true">Tout <span>{{ $products->count() }}</span></button>
                        @foreach ($categories as $category)
                            <button type="button" class="pos-chip" data-pos-category="{{ $category->id }}" aria-pressed="false">{{ $category->nom }} <span>{{ $products->where('category_id', $category->id)->count() }}</span></button>
                        @endforeach
                    </div>
                </div>

                <div class="pos-product-grid" data-pos-products>
                    @foreach ($products as $product)
                        @php($isLowStock = (float) $product->quantite_stock <= (float) $product->seuil_alerte)
                        <button type="button" class="pos-product"
                            data-pos-product
                            data-id="{{ $product->id }}"
                            data-name="{{ $product->nom }}"
                            data-reference="{{ $product->reference }}"
                            data-price="{{ $product->prix_vente }}"
                            data-stock="{{ $product->quantite_stock }}"
                            data-unit="{{ $product->unit->symbole }}"
                            data-category="{{ $product->category_id }}"
                            data-search="{{ Str::lower($product->nom.' '.$product->reference) }}">
                            <span class="pos-product-top">
                                <span class="pos-product-category">{{ $product->category?->nom }}</span>
                                <span class="pos-product-in-cart" data-pos-in-cart hidden></span>
                            </span>
                            <strong class="pos-product-name">{{ $product->nom }}</strong>
                            <small class="pos-product-ref">{{ $product->reference }}</small>
                            <span class="pos-product-bottom">
                                <span class="pos-product-price">{{ number_format((float) $product->prix_vente, 0, ',', ' ') }} <small>FCFA</small></span>
                                <span class="pos-product-stock {{ $isLowStock ? 'is-low' : '' }}">{{ rtrim(rtrim(number_format((float) $product->quantite_stock, 3, ',', ' '), '0'), ',') }} {{ $product->unit->symbole }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
                <div class="pos-empty-result" data-pos-no-result hidden>
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <p class="mb-0">Aucun produit ne correspond à votre recherche.</p>
                </div>
            </section>

            <aside class="card pos-cart" aria-labelledby="pos-cart-title">
                <div class="pos-cart-header">
                    <h2 class="h6 mb-0" id="pos-cart-title"><i class="bi bi-cart3" aria-hidden="true"></i> Panier <span class="pos-cart-count" data-pos-count>0</span></h2>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" data-pos-clear hidden>Vider</button>
                </div>

                <div class="pos-cart-lines" data-pos-lines aria-live="polite"></div>
                <div class="pos-cart-empty" data-pos-empty>
                    <i class="bi bi-cart-plus" aria-hidden="true"></i>
                    <strong>Le panier est vide</strong>
                    <span>Sélectionnez des produits dans la liste.</span>
                </div>
                <div data-pos-hidden-items></div>

                <div class="pos-checkout">
                    <div class="row g-2">
                        <div class="col-12">
                            <label for="customer_id" class="form-label">Client</label>
                            <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" data-pos-customer>
                                <option value="">Client de passage</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id', request('customer_id')) == $customer->id)>{{ $customer->nom }}{{ $customer->telephone ? ' · '.$customer->telephone : '' }}</option>
                                @endforeach
                            </select>
                            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12" data-pos-walk-in-name>
                            <label for="client_nom" class="visually-hidden">Nom du client de passage</label>
                            <input type="text" name="client_nom" id="client_nom" value="{{ old('client_nom') }}" maxlength="150" class="form-control form-control-sm" placeholder="Nom du client de passage (facultatif)">
                        </div>
                    </div>

                    <fieldset class="mt-3">
                        <legend class="form-label mb-2">Moyen de paiement</legend>
                        <div class="pos-payment-methods">
                            @foreach ($paymentMethods as $value => $label)
                                <input type="radio" class="btn-check" name="moyen_paiement" id="payment-{{ $value }}" value="{{ $value }}" @checked(old('moyen_paiement', 'especes') === $value) required>
                                <label class="pos-payment-method" for="payment-{{ $value }}"><i class="bi {{ ['especes' => 'bi-cash-stack', 'mobile_money' => 'bi-phone', 'carte' => 'bi-credit-card', 'autre' => 'bi-three-dots'][$value] ?? 'bi-wallet2' }}" aria-hidden="true"></i>{{ $label }}</label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="pos-total">
                        <span>Total à payer</span>
                        <strong data-pos-total>0 FCFA</strong>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="montant_recu" class="form-label mb-0">Montant reçu (FCFA)</label>
                            <button type="button" class="btn btn-link btn-sm p-0" data-pos-exact>Montant exact</button>
                        </div>
                        <input type="number" name="montant_recu" id="montant_recu" value="{{ old('montant_recu', 0) }}" min="0" step="0.01" class="form-control form-control-lg @error('montant_recu') is-invalid @enderror" data-pos-received @if (old('montant_recu') !== null) data-pos-edited @endif required>
                        @error('montant_recu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="pos-quick-amounts" data-pos-quick-amounts></div>
                    </div>

                    <div class="pos-change" data-pos-change-box>
                        <span data-pos-change-label>Monnaie à rendre</span>
                        <strong data-pos-change>0 FCFA</strong>
                    </div>
                    <p class="pos-partial-hint" data-pos-partial-hint hidden><i class="bi bi-info-circle" aria-hidden="true"></i> Paiement partiel : sélectionnez un client enregistré.</p>

                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-3" data-pos-submit disabled><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Valider la vente</button>
                </div>
            </aside>

            <button type="button" class="pos-mobile-bar" data-pos-mobile-bar hidden>
                <span class="pos-mobile-bar-count"><i class="bi bi-cart3" aria-hidden="true"></i><span data-pos-mobile-count>0</span></span>
                <span class="pos-mobile-bar-total"><small>Total</small><strong data-pos-mobile-total>0 FCFA</strong></span>
                <span class="pos-mobile-bar-action">Voir le panier <i class="bi bi-chevron-down" aria-hidden="true"></i></span>
            </button>
        </form>
    @endif
</div>
@endsection
