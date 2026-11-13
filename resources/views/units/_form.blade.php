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

<form action="{{ isset($unit) ? route('unites.update', $unit) : route('unites.store') }}" method="POST">
    @csrf
    @isset($unit)
        @method('PUT')
    @endisset

    <div class="row g-3">
        <div class="col-md-6">
            <label for="nom" class="form-label">Nom de l’unité <span class="text-danger">*</span></label>
            <input type="text" name="nom" id="nom" value="{{ old('nom', $unit->nom ?? '') }}" class="form-control @error('nom') is-invalid @enderror" maxlength="100" required>
            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="symbole" class="form-label">Symbole <span class="text-danger">*</span></label>
            <input type="text" name="symbole" id="symbole" value="{{ old('symbole', $unit->symbole ?? '') }}" class="form-control @error('symbole') is-invalid @enderror" maxlength="30" required>
            @error('symbole')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <input type="hidden" name="statut" value="0">
            <div class="form-check">
                <input type="checkbox" name="statut" id="statut" value="1" class="form-check-input" @checked(old('statut', $unit->statut ?? true))>
                <label for="statut" class="form-check-label">Unité active et sélectionnable sur les produits</label>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ isset($unit) ? 'Enregistrer les modifications' : 'Créer l’unité' }}</button>
        <a href="{{ route('unites.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
