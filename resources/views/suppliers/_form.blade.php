@if ($errors->any())<div class="alert alert-danger"><strong>Vérifiez les informations saisies.</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<form action="{{ isset($supplier) ? route('fournisseurs.update', $supplier) : route('fournisseurs.store') }}" method="POST">
    @csrf
    @isset($supplier) @method('PUT') @endisset
    <div class="row g-3">
        <div class="col-12 col-md-6"><label for="nom" class="form-label">Nom de l’entreprise <span class="text-danger">*</span></label><input type="text" name="nom" id="nom" value="{{ old('nom', $supplier->nom ?? '') }}" maxlength="150" class="form-control @error('nom') is-invalid @enderror" required>@error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12 col-md-6"><label for="contact_nom" class="form-label">Personne à contacter</label><input type="text" name="contact_nom" id="contact_nom" value="{{ old('contact_nom', $supplier->contact_nom ?? '') }}" maxlength="150" class="form-control @error('contact_nom') is-invalid @enderror">@error('contact_nom')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12 col-md-6"><label for="telephone" class="form-label">Téléphone</label><input type="tel" name="telephone" id="telephone" value="{{ old('telephone', $supplier->telephone ?? '') }}" maxlength="30" class="form-control @error('telephone') is-invalid @enderror">@error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12 col-md-6"><label for="email" class="form-label">Courriel</label><input type="email" name="email" id="email" value="{{ old('email', $supplier->email ?? '') }}" maxlength="150" class="form-control @error('email') is-invalid @enderror">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><label for="adresse" class="form-label">Adresse</label><textarea name="adresse" id="adresse" rows="3" maxlength="2000" class="form-control @error('adresse') is-invalid @enderror">{{ old('adresse', $supplier->adresse ?? '') }}</textarea>@error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><input type="hidden" name="statut" value="0"><div class="form-check"><input type="checkbox" name="statut" id="statut" value="1" class="form-check-input" @checked(old('statut', $supplier->statut ?? true))><label for="statut" class="form-check-label">Fournisseur actif et sélectionnable lors d’un achat</label></div></div>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-4"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i>{{ isset($supplier) ? 'Enregistrer les modifications' : 'Créer le fournisseur' }}</button><a href="{{ route('fournisseurs.index') }}" class="btn btn-outline-secondary">Annuler</a></div>
</form>
