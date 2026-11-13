@if ($errors->any())
    <div class="alert alert-danger"><strong>Vérifiez les informations saisies.</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label" for="name">Nom <span class="text-danger">*</span></label>
        <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required maxlength="255">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label" for="email">Courriel <span class="text-danger">*</span></label>
        <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required maxlength="255" autocomplete="off">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="role">Rôle <span class="text-danger">*</span></label>
        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
            <option value="vendeur" @selected(old('role', $user->role ?? 'vendeur') === 'vendeur')>Vendeur</option>
            <option value="admin" @selected(old('role', $user->role ?? 'vendeur') === 'admin')>Administrateur</option>
        </select>
        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Les vendeurs gèrent les ventes et les clients. Les administrateurs accèdent aussi aux réglages, achats, retours et rapports.</div>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label" for="password">{{ isset($editing) ? 'Nouveau mot de passe (facultatif)' : 'Mot de passe' }}@unless (isset($editing)) <span class="text-danger">*</span>@endunless</label>
        <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" @required(! isset($editing)) autocomplete="new-password">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">12 caractères minimum, avec majuscule, minuscule et chiffre.</div>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" @required(! isset($editing)) autocomplete="new-password">
    </div>
</div>
