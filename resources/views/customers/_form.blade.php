@if ($errors->any())<div class="alert alert-danger"><strong>Vérifiez les informations saisies.</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<form action="{{ isset($customer) ? route('clients.update', $customer) : route('clients.store') }}" method="POST">
    @csrf
    @isset($customer) @method('PUT') @endisset
    <div class="row g-3">
        <div class="col-12 col-md-6"><label for="nom" class="form-label">Nom du client <span class="text-danger">*</span></label><input type="text" name="nom" id="nom" value="{{ old('nom', $customer->nom ?? '') }}" maxlength="150" class="form-control @error('nom') is-invalid @enderror" required>@error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12 col-md-6"><label for="telephone" class="form-label">Téléphone</label><input type="tel" name="telephone" id="telephone" value="{{ old('telephone', $customer->telephone ?? '') }}" maxlength="30" class="form-control @error('telephone') is-invalid @enderror">@error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12 col-md-6"><label for="email" class="form-label">Courriel</label><input type="email" name="email" id="email" value="{{ old('email', $customer->email ?? '') }}" maxlength="150" class="form-control @error('email') is-invalid @enderror">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><label for="adresse" class="form-label">Adresse</label><textarea name="adresse" id="adresse" rows="3" maxlength="2000" class="form-control @error('adresse') is-invalid @enderror">{{ old('adresse', $customer->adresse ?? '') }}</textarea>@error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><input type="hidden" name="statut" value="0"><div class="form-check"><input type="checkbox" name="statut" id="statut" value="1" class="form-check-input" @checked(old('statut', $customer->statut ?? true))><label for="statut" class="form-check-label">Fiche active et sélectionnable lors d’une vente</label></div></div>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-4"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i>{{ isset($customer) ? 'Enregistrer les modifications' : 'Créer le client' }}</button><a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Annuler</a></div>
</form>
