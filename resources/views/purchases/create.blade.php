@extends('layouts.app')

@section('title', 'Nouvel achat')

@section('content')
<div class="container-fluid">
    <div class="mb-4"><a href="{{ route('achats.index') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour aux achats</a><h1 class="h3 mt-3 mb-1">Nouvel achat fournisseur</h1><p class="text-muted mb-0">La réception de l’achat augmente le stock et actualise le dernier prix d’achat des produits.</p></div>
    @if ($errors->any())<div class="alert alert-danger"><strong>L’achat n’a pas été enregistré.</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if ($suppliers->isEmpty() || $products->isEmpty())
        <div class="card"><div class="card-body text-center py-5"><i class="bi bi-bag-x display-6 text-muted" aria-hidden="true"></i><h2 class="h5 mt-3">Préparez vos données avant l’achat</h2><p class="text-muted">Un achat nécessite au moins un fournisseur actif et un produit actif.</p><div class="d-flex flex-wrap justify-content-center gap-2"><a href="{{ route('fournisseurs.create') }}" class="btn btn-outline-primary"><i class="bi bi-truck" aria-hidden="true"></i>Ajouter un fournisseur</a><a href="{{ route('produits.create') }}" class="btn btn-primary"><i class="bi bi-box-seam" aria-hidden="true"></i>Ajouter un produit</a></div></div></div>
    @else
        @php($oldItems = old('items', [['product_id' => '', 'quantite' => 1, 'prix_unitaire' => '']]))
        <form action="{{ route('achats.store') }}" method="POST" data-purchase-form>
            @csrf
            <div class="row g-4 align-items-start">
                <div class="col-12 col-xl-8"><div class="card"><div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3"><h2 class="h6 mb-0">Produits réceptionnés</h2><button type="button" class="btn btn-sm btn-outline-primary" data-add-purchase-item><i class="bi bi-plus-lg" aria-hidden="true"></i>Ajouter un produit</button></div><div class="card-body"><div data-purchase-items>
                    @foreach ($oldItems as $index => $oldItem)
                        <div class="purchase-item-row border rounded-3 p-3 mb-3" data-purchase-item>
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-lg-6"><label for="items_{{ $index }}_product" class="form-label">Produit <span class="text-danger">*</span></label><select id="items_{{ $index }}_product" name="items[{{ $index }}][product_id]" class="form-select" data-purchase-product required><option value="">Sélectionner un produit</option>@foreach ($products as $product)<option value="{{ $product->id }}" data-price="{{ $product->prix_achat }}" data-unit="{{ $product->unit->symbole }}" @selected(($oldItem['product_id'] ?? '') == $product->id)>{{ $product->reference }} · {{ $product->nom }} ({{ $product->unit->symbole }})</option>@endforeach</select></div>
                                <div class="col-6 col-lg-2"><label for="items_{{ $index }}_quantity" class="form-label">Quantité</label><input id="items_{{ $index }}_quantity" name="items[{{ $index }}][quantite]" type="number" min="0.001" max="999999999.999" step="0.001" value="{{ $oldItem['quantite'] ?? 1 }}" class="form-control" data-purchase-quantity required></div>
                                <div class="col-6 col-lg-3"><label for="items_{{ $index }}_price" class="form-label">Prix d’achat unitaire</label><input id="items_{{ $index }}_price" name="items[{{ $index }}][prix_unitaire]" type="number" min="0" max="9999999999.99" step="0.01" value="{{ $oldItem['prix_unitaire'] ?? '' }}" class="form-control" data-purchase-price required></div>
                                <div class="col-9 col-lg-1"><span class="form-label d-block">Total</span><strong data-purchase-line-total class="small text-nowrap">0 FCFA</strong></div>
                                <div class="col-3 d-lg-none text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-purchase-item aria-label="Retirer ce produit"><i class="bi bi-trash" aria-hidden="true"></i></button></div>
                                <div class="col-lg-12 d-none d-lg-flex justify-content-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-purchase-item><i class="bi bi-trash" aria-hidden="true"></i>Retirer</button></div>
                            </div>
                        </div>
                    @endforeach
                </div></div></div></div>
                <div class="col-12 col-xl-4"><div class="card"><div class="card-header py-3"><h2 class="h6 mb-0">Fournisseur et total</h2></div><div class="card-body">
                    <div class="mb-3"><label for="supplier_id" class="form-label">Fournisseur <span class="text-danger">*</span></label><select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required><option value="">Choisir un fournisseur</option>@foreach ($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id', request('supplier_id')) == $supplier->id)>{{ $supplier->nom }}</option>@endforeach</select>@error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="purchase-total-box"><span>Total réceptionné</span><strong data-purchase-total>0 FCFA</strong></div>
                    <div class="mt-3"><label for="montant_paye" class="form-label">Montant réglé maintenant</label><input id="montant_paye" name="montant_paye" type="number" min="0" step="0.01" value="{{ old('montant_paye') }}" class="form-control" placeholder="Laisser vide pour tout régler"><div class="form-text">Le solde restera dû au fournisseur.</div></div>
                    <div class="mt-3"><label for="moyen_paiement" class="form-label">Moyen de paiement</label><select id="moyen_paiement" name="moyen_paiement" class="form-select">@foreach ($paymentMethods as $value => $label)<option value="{{ $value }}" @selected(old('moyen_paiement', 'especes') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <p class="text-muted small mt-3 mb-0">L’achat est enregistré comme reçu et ses quantités sont ajoutées au stock immédiatement.</p>
                    <button type="submit" class="btn btn-primary w-100 mt-4"><i class="bi bi-check-lg" aria-hidden="true"></i>Enregistrer la réception</button>
                    <a href="{{ route('achats.index') }}" class="btn btn-outline-secondary w-100 mt-2">Annuler</a>
                </div></div></div>
            </div>
            <template data-purchase-item-template><div class="purchase-item-row border rounded-3 p-3 mb-3" data-purchase-item><div class="row g-3 align-items-end">
                <div class="col-12 col-lg-6"><label for="items___INDEX___product" class="form-label">Produit <span class="text-danger">*</span></label><select id="items___INDEX___product" name="items[__INDEX__][product_id]" class="form-select" data-purchase-product required><option value="">Sélectionner un produit</option>@foreach ($products as $product)<option value="{{ $product->id }}" data-price="{{ $product->prix_achat }}" data-unit="{{ $product->unit->symbole }}">{{ $product->reference }} · {{ $product->nom }} ({{ $product->unit->symbole }})</option>@endforeach</select></div>
                <div class="col-6 col-lg-2"><label for="items___INDEX___quantity" class="form-label">Quantité</label><input id="items___INDEX___quantity" name="items[__INDEX__][quantite]" type="number" min="0.001" max="999999999.999" step="0.001" value="1" class="form-control" data-purchase-quantity required></div>
                <div class="col-6 col-lg-3"><label for="items___INDEX___price" class="form-label">Prix d’achat unitaire</label><input id="items___INDEX___price" name="items[__INDEX__][prix_unitaire]" type="number" min="0" max="9999999999.99" step="0.01" value="" class="form-control" data-purchase-price required></div>
                <div class="col-9 col-lg-1"><span class="form-label d-block">Total</span><strong data-purchase-line-total class="small text-nowrap">0 FCFA</strong></div>
                <div class="col-3 d-lg-none text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-purchase-item aria-label="Retirer ce produit"><i class="bi bi-trash" aria-hidden="true"></i></button></div>
                <div class="col-lg-12 d-none d-lg-flex justify-content-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-purchase-item><i class="bi bi-trash" aria-hidden="true"></i>Retirer</button></div>
            </div></div></template>
        </form>
    @endif
</div>
@endsection
