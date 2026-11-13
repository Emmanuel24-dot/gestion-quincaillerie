@if ($errors->any())
    <div class="alert alert-danger"><strong>Vérifiez les informations saisies.</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form action="{{ isset($category) ? route('categories.update', $category) : route('categories.store') }}" method="POST">
    @csrf
    @isset($category) @method('PUT') @endisset
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="nom" class="form-label">Nom de la catégorie <span class="text-danger">*</span></label>
            <input type="text" name="nom" id="nom" value="{{ old('nom', $category->nom ?? '') }}" maxlength="100" class="form-control @error('nom') is-invalid @enderror" placeholder="Ex. Ciment, Plomberie, Outillage" required>
            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" rows="3" maxlength="2000" class="form-control @error('description') is-invalid @enderror" placeholder="Facultatif">{{ old('description', $category->description ?? '') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <input type="hidden" name="statut" value="0">
            <div class="form-check">
                <input type="checkbox" name="statut" id="statut" value="1" class="form-check-input" @checked(old('statut', $category->statut ?? true))>
                <label for="statut" class="form-check-label">Catégorie active</label>
            </div>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>{{ isset($category) ? 'Enregistrer les modifications' : 'Créer la catégorie' }}</button>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
