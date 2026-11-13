@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Vérifiez les informations saisies.</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ isset($product) ? route('produits.update', $product) : route('produits.store') }}" method="POST">
    @csrf
    @isset($product)
        @method('PUT')
    @endisset

    <div class="row g-3">
        <div class="col-md-6">
            <label for="reference" class="form-label">Référence</label>
            @isset($product)
                <input type="text" id="reference" value="{{ $product->reference }}" class="form-control bg-light" readonly>
                <div class="form-text">Référence attribuée automatiquement, elle ne peut pas être modifiée.</div>
            @else
                <input type="text" id="reference" value="Générée automatiquement à l’enregistrement" class="form-control bg-light" readonly>
                <div class="form-text">La référence sera créée après l’enregistrement du produit.</div>
            @endisset
        </div>
        <div class="col-md-6">
            <label for="nom" class="form-label">Nom du produit <span class="text-danger">*</span></label>
            <input type="text" name="nom" id="nom" value="{{ old('nom', $product->nom ?? '') }}" class="form-control @error('nom') is-invalid @enderror" maxlength="150" required>
            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="category_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">Choisir une catégorie</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->nom }}</option>
                @endforeach
            </select>
            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="unit_id" class="form-label">Unité <span class="text-danger">*</span></label>
            <select name="unit_id" id="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                <option value="">Choisir une unité</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected(old('unit_id', $product->unit_id ?? '') == $unit->id)>{{ $unit->nom }} ({{ $unit->symbole }})</option>
                @endforeach
            </select>
            @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description ?? '') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="prix_achat" class="form-label">Prix d’achat (FCFA) <span class="text-danger">*</span></label>
            <input type="number" name="prix_achat" id="prix_achat" value="{{ old('prix_achat', $product->prix_achat ?? '') }}" class="form-control @error('prix_achat') is-invalid @enderror" min="0" step="0.01" required>
            @error('prix_achat')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="prix_vente" class="form-label">Prix de vente (FCFA) <span class="text-danger">*</span></label>
            <input type="number" name="prix_vente" id="prix_vente" value="{{ old('prix_vente', $product->prix_vente ?? '') }}" class="form-control @error('prix_vente') is-invalid @enderror" min="0" step="0.01" required>
            @error('prix_vente')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        @if (isset($product))
            <div class="col-md-6">
                <label class="form-label" for="stock-actuel">Stock actuel</label>
                <input type="text" id="stock-actuel" class="form-control" value="{{ number_format((float) $product->quantite_stock, 3, ',', ' ') }} {{ $product->unit->symbole }}" readonly>
                <div class="form-text">Pour modifier le stock, enregistrez un mouvement de stock.</div>
            </div>
        @else
            <div class="col-md-6">
                <label for="quantite_stock" class="form-label">Stock initial <span class="text-danger">*</span></label>
                <input type="number" name="quantite_stock" id="quantite_stock" value="{{ old('quantite_stock', 0) }}" class="form-control @error('quantite_stock') is-invalid @enderror" min="0" step="0.001" required>
                @error('quantite_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Cette quantité sera enregistrée comme mouvement initial.</div>
            </div>
        @endif
        <div class="col-md-6">
            <label for="seuil_alerte" class="form-label">Seuil d’alerte <span class="text-danger">*</span></label>
            <input type="number" name="seuil_alerte" id="seuil_alerte" value="{{ old('seuil_alerte', $product->seuil_alerte ?? 0) }}" class="form-control @error('seuil_alerte') is-invalid @enderror" min="0" step="0.001" required>
            @error('seuil_alerte')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <input type="hidden" name="statut" value="0">
            <div class="form-check">
                <input type="checkbox" name="statut" id="statut" value="1" class="form-check-input" @checked(old('statut', $product->statut ?? true))>
                <label for="statut" class="form-check-label">Produit actif</label>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ isset($product) ? 'Enregistrer les modifications' : 'Créer le produit' }}</button>
        <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
