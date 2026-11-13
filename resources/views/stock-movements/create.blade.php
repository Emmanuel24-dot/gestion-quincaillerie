@extends('layouts.app')

@section('title', 'Nouveau mouvement de stock')

@section('content')
<div class="container-fluid">
    <div class="mb-4"><a href="{{ route('mouvements-stock.index') }}" class="back-link"><i class="bi bi-arrow-left me-1"></i>Retour à l’historique</a><h1 class="h3 mt-2 mb-1">Nouveau mouvement de stock</h1><p class="text-muted mb-0">Chaque opération est conservée dans l’historique.</p></div>

    @if ($errors->any())
        <div class="alert alert-danger"><strong>Le mouvement n’a pas été enregistré.</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        @if ($products->isEmpty())
            <div class="text-center py-4"><h2 class="h5">Aucun produit actif</h2><p class="text-muted">Créez d’abord un produit actif pour enregistrer un mouvement.</p><a href="{{ route('produits.create') }}" class="btn btn-primary">Créer un produit</a></div>
        @else
            <form action="{{ route('mouvements-stock.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"><label for="product_id" class="form-label">Produit <span class="text-danger">*</span></label><select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required><option value="">Choisir un produit</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected(old('product_id', $selectedProductId) == $product->id)>{{ $product->reference }} · {{ $product->nom }} ({{ number_format((float) $product->quantite_stock, 3, ',', ' ') }} {{ $product->unit->symbole }})</option>@endforeach</select>@error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label for="type" class="form-label">Opération <span class="text-danger">*</span></label><select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required><option value="">Choisir une opération</option>@foreach ($types as $value => $label)<option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>@endforeach</select>@error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label for="quantite" class="form-label">Quantité <span class="text-danger">*</span></label><input type="number" name="quantite" id="quantite" value="{{ old('quantite') }}" class="form-control @error('quantite') is-invalid @enderror" min="0.001" max="999999999.999" step="0.001" required>@error('quantite')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label for="motif" class="form-label">Motif <span class="text-danger">*</span></label><input type="text" name="motif" id="motif" value="{{ old('motif') }}" class="form-control @error('motif') is-invalid @enderror" maxlength="150" placeholder="Ex. réception fournisseur, casse, correction d’inventaire" required>@error('motif')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><label for="notes" class="form-label">Notes</label><textarea name="notes" id="notes" rows="3" maxlength="2000" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer le mouvement</button><a href="{{ route('mouvements-stock.index') }}" class="btn btn-outline-secondary">Annuler</a></div>
            </form>
        @endif
    </div></div>
</div>
@endsection

